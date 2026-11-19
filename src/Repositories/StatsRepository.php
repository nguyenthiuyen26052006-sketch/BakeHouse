<?php

declare(strict_types=1);

namespace App\Repositories;

/** Shop-wide dashboard statistics. */
final class StatsRepository extends BaseRepository
{
    /** @return array<string, int> */
    public function summary(): array
    {
        $totalOrders = (int) $this->fetchValue('SELECT COUNT(*) FROM orders');
        $completed = (int) $this->fetchValue('SELECT COUNT(*) FROM orders WHERE status = \'delivered\'');
        $sold = $this->fetchOne(
            'SELECT COALESCE(SUM(oi.quantity), 0) AS qty, COALESCE(SUM(oi.unit_price * oi.quantity), 0) AS revenue,
                    COUNT(DISTINCT oi.order_id) AS orders
             FROM order_items oi WHERE oi.status = \'delivered\''
        ) ?? ['qty' => 0, 'revenue' => 0, 'orders' => 0];

        $revenue = (int) $sold['revenue'];
        $deliveredOrders = (int) $sold['orders'];

        return [
            'total_orders' => $totalOrders,
            'completed_orders' => $completed,
            'in_progress_orders' => $totalOrders - $completed,
            'items_sold' => (int) $sold['qty'],
            'revenue' => $revenue,
            'average_revenue' => $deliveredOrders > 0 ? intdiv($revenue, $deliveredOrders) : 0,
        ];
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

    /** @return array<int, array<string, mixed>> */
    public function recentActivity(int $limit): array
    {
        return $this->fetchAll(
            'SELECT h.created_at, h.to_status, o.order_code
             FROM order_status_histories h JOIN orders o ON o.id = h.order_id
             ORDER BY h.id DESC LIMIT :limit',
            ['limit' => $limit]
        );
    }

    /** @return array<string, int> month (Y-m) => revenue, last 12 months, zero filled */
    public function revenueByMonth(): array
    {
        $rows = $this->fetchAll(
            'SELECT DATE_FORMAT(o.created_at, \'%Y-%m\') AS bucket, SUM(oi.unit_price * oi.quantity) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE oi.status = \'delivered\' AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
             GROUP BY bucket'
        );
        $map = array_column($rows, 'revenue', 'bucket');
        $series = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("first day of -{$i} month"));
            $series[$key] = (int) ($map[$key] ?? 0);
        }

        return $series;
    }

    /** @return array<string, int> day (Y-m-d) => revenue, last 30 days, zero filled */
    public function revenueByDay(): array
    {
        $rows = $this->fetchAll(
            'SELECT DATE(o.created_at) AS bucket, SUM(oi.unit_price * oi.quantity) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE oi.status = \'delivered\' AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY bucket'
        );
        $map = array_column($rows, 'revenue', 'bucket');
        $series = [];
        for ($i = 29; $i >= 0; $i--) {
            $key = date('Y-m-d', strtotime("-{$i} day"));
            $series[$key] = (int) ($map[$key] ?? 0);
        }

        return $series;
    }

    /** @return array<int, array<string, mixed>> */
    public function topSelling(int $limit): array
    {
        return $this->fetchAll(
            'SELECT product_name, SUM(quantity) AS qty FROM order_items
             GROUP BY product_id, product_name ORDER BY qty DESC LIMIT :limit',
            ['limit' => $limit]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function topReviewed(int $limit): array
    {
        return $this->fetchAll(
            'SELECT name, rating_count, rating_avg FROM products
             WHERE rating_count > 0 ORDER BY rating_count DESC, rating_avg DESC LIMIT :limit',
            ['limit' => $limit]
        );
    }
}
