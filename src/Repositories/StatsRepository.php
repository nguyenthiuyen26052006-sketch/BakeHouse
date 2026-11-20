<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\ReportPeriod;

/** Sales statistics for the admin dashboard and the printable report. */
final class StatsRepository extends BaseRepository
{
    private const DAILY_MAX_DAYS = 62;

    /** @return array{0: string, 1: array<string, string>} WHERE fragment on orders o + params for the period */
    private function window(ReportPeriod $period): array
    {
        if ($period->from === null) {
            return ['1 = 1', []];
        }

        return ['o.created_at >= :from AND o.created_at < DATE_ADD(:to, INTERVAL 1 DAY)', ['from' => $period->from, 'to' => $period->to]];
    }

    /** @return array{revenue: int, orders: int, delivered_orders: int, items_sold: int, average: int} */
    public function summary(ReportPeriod $period): array
    {
        [$where, $params] = $this->window($period);
        $orders = $this->fetchOne(
            "SELECT COUNT(*) AS total, COALESCE(SUM(o.status = 'delivered'), 0) AS delivered FROM orders o WHERE {$where}",
            $params
        ) ?? ['total' => 0, 'delivered' => 0];
        $sold = $this->fetchOne(
            "SELECT COALESCE(SUM(oi.quantity), 0) AS qty, COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE oi.status = 'delivered' AND {$where}",
            $params
        ) ?? ['qty' => 0, 'revenue' => 0];

        $revenue = (int) $sold['revenue'];
        $delivered = (int) $orders['delivered'];

        return [
            'revenue' => $revenue,
            'orders' => (int) $orders['total'],
            'delivered_orders' => $delivered,
            'items_sold' => (int) $sold['qty'],
            'average' => $delivered > 0 ? intdiv($revenue, $delivered) : 0,
        ];
    }

    /**
     * Revenue per day (periods up to ~2 months) or per month (longer), zero filled.
     *
     * @return array{bucket: string, series: array<string, int>}
     */
    public function series(ReportPeriod $period): array
    {
        [$where, $params] = $this->window($period);
        $from = $period->from ?? (string) ($this->fetchValue('SELECT DATE(MIN(created_at)) FROM orders') ?: date('Y-m-d'));
        $to = $period->to ?? date('Y-m-d');
        $days = (int) ((strtotime($to) - strtotime($from)) / 86400) + 1;
        $daily = $days <= self::DAILY_MAX_DAYS;
        $format = $daily ? '%Y-%m-%d' : '%Y-%m';

        $rows = $this->fetchAll(
            "SELECT DATE_FORMAT(o.created_at, '{$format}') AS bucket, SUM(oi.unit_price * oi.quantity) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE oi.status = 'delivered' AND {$where} GROUP BY bucket",
            $params
        );
        $map = array_column($rows, 'revenue', 'bucket');

        $series = [];
        if ($daily) {
            for ($t = strtotime($from); $t <= strtotime($to); $t += 86400) {
                $key = date('Y-m-d', $t);
                $series[$key] = (int) ($map[$key] ?? 0);
            }
        } else {
            for ($t = strtotime(date('Y-m-01', strtotime($from))); $t <= strtotime($to); $t = strtotime('+1 month', $t)) {
                $key = date('Y-m', $t);
                $series[$key] = (int) ($map[$key] ?? 0);
            }
        }

        return ['bucket' => $daily ? 'day' : 'month', 'series' => $series];
    }

    /** @return array<int, array{product_name: string, qty: int, revenue: int}> */
    public function topSelling(ReportPeriod $period, int $limit): array
    {
        [$where, $params] = $this->window($period);
        $params['limit'] = $limit;

        return $this->fetchAll(
            "SELECT oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.unit_price * oi.quantity) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE oi.status = 'delivered' AND {$where}
             GROUP BY oi.product_id, oi.product_name ORDER BY qty DESC, revenue DESC LIMIT :limit",
            $params
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function recentOrders(int $limit): array
    {
        return $this->fetchAll(
            'SELECT id, order_code, recipient_name, status, created_at, total_amount
             FROM orders ORDER BY id DESC LIMIT :limit',
            ['limit' => $limit]
        );
    }

    /** Things that need the shop owner's attention right now (independent of the selected period). @return array<string, int> */
    public function attention(): array
    {
        return [
            'pending_orders' => (int) $this->fetchValue("SELECT COUNT(*) FROM orders WHERE status = 'pending'"),
            'shipping_orders' => (int) $this->fetchValue("SELECT COUNT(*) FROM orders WHERE status = 'shipped'"),
            'restock' => (int) $this->fetchValue(
                'SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock_quantity <= ' . ProductRepository::RESTOCK_AT
            ),
            'unanswered_reviews' => (int) $this->fetchValue(
                'SELECT COUNT(*) FROM reviews WHERE (admin_reply IS NULL OR admin_reply = \'\')'
            ),
        ];
    }
}
