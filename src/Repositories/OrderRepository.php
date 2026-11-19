<?php

declare(strict_types=1);

namespace App\Repositories;

final class OrderRepository extends BaseRepository
{
    /** @param array<string, mixed> $data */
    public function createOrder(array $data): int
    {
        $columns = array_keys($data);
        $this->execute(
            sprintf(
                'INSERT INTO orders (%s) VALUES (%s)',
                implode(', ', $columns),
                implode(', ', array_fill(0, count($columns), '?'))
            ),
            array_values($data)
        );

        return (int) $this->db()->lastInsertId();
    }

    public function createItem(int $orderId, array $product, int $quantity): void
    {
        $this->execute(
            'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity)
             VALUES (?, ?, ?, ?, ?)',
            [$orderId, $product['id'], $product['name'], $product['price'], $quantity]
        );
    }

    public function createHistory(int $orderId, ?string $from, string $to, ?int $changedBy): void
    {
        $this->execute(
            'INSERT INTO order_status_histories (order_id, from_status, to_status, changed_by) VALUES (?, ?, ?, ?)',
            [$orderId, $from, $to, $changedBy]
        );
    }

    /** Lock product rows for stock update inside a transaction. */
    public function lockProduct(int $productId): ?array
    {
        return $this->fetchOne('SELECT * FROM products WHERE id = ? AND is_active = 1 FOR UPDATE', [$productId]);
    }

    public function decrementStock(int $productId, int $quantity): void
    {
        $this->execute('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?', [$quantity, $productId]);
    }

    public function updateOrderCode(int $orderId, string $code): void
    {
        $this->execute('UPDATE orders SET order_code = ? WHERE id = ?', [$code, $orderId]);
    }

    public function findByCode(string $code): ?array
    {
        return $this->fetchOne('SELECT * FROM orders WHERE order_code = ?', [$code]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM orders WHERE id = ?', [$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function items(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT oi.*, p.slug AS product_slug, p.image_path
             FROM order_items oi
             LEFT JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ? ORDER BY oi.id',
            [$orderId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function histories(int $orderId): array
    {
        return $this->fetchAll(
            'SELECT h.*, COALESCE(NULLIF(p.full_name, \'\'), u.email) AS changed_by_name
             FROM order_status_histories h
             LEFT JOIN users u ON u.id = h.changed_by
             LEFT JOIN user_profiles p ON p.user_id = h.changed_by
             WHERE h.order_id = ? ORDER BY h.id',
            [$orderId]
        );
    }

    public function countByUser(int $userId): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM orders WHERE user_id = ?', [$userId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function listByUser(int $userId, int $limit, int $offset): array
    {
        return $this->fetchAll(
            'SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count
             FROM orders o WHERE o.user_id = :uid ORDER BY o.id DESC LIMIT :limit OFFSET :offset',
            ['uid' => $userId, 'limit' => $limit, 'offset' => $offset]
        );
    }

    public function updateStatus(int $orderId, string $status): void
    {
        $this->execute('UPDATE orders SET status = ? WHERE id = ?', [$status, $orderId]);
    }

    public function updatePaymentStatus(int $orderId, string $status): void
    {
        $this->execute('UPDATE orders SET payment_status = ? WHERE id = ?', [$status, $orderId]);
    }

    public function updateAllItemsStatus(int $orderId, string $status): void
    {
        $this->execute('UPDATE order_items SET status = ? WHERE order_id = ?', [$status, $orderId]);
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function adminWhere(string $search, string $status): array
    {
        $clauses = [];
        $params = [];
        if ($search !== '') {
            $clauses[] = '(o.order_code LIKE :q OR o.recipient_name LIKE :q2 OR o.shipping_phone LIKE :q3)';
            $params['q'] = $params['q2'] = $params['q3'] = $this->likeTerm($search);
        }
        if ($status === 'in_progress') {
            $clauses[] = "o.status <> 'delivered'";
        } elseif (in_array($status, ['pending', 'processing', 'shipped', 'delivered'], true)) {
            $clauses[] = 'o.status = :status';
            $params['status'] = $status;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    public function adminCount(string $search, string $status): int
    {
        [$where, $params] = $this->adminWhere($search, $status);

        return (int) $this->fetchValue("SELECT COUNT(*) FROM orders o {$where}", $params);
    }

    /** @return array<int, array<string, mixed>> */
    public function adminList(string $search, string $status, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminWhere($search, $status);
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            "SELECT o.* FROM orders o {$where} ORDER BY o.id DESC LIMIT :limit OFFSET :offset",
            $params
        );
    }

    // ---------------------------------------------------------------- reports

    /** @return array{0: string, 1: array<string, mixed>} */
    private function reportWhere(string $search, string $month): array
    {
        $clauses = [];
        $params = [];
        if ($search !== '') {
            $clauses[] = '(o.order_code LIKE :q OR o.recipient_name LIKE :q2 OR o.shipping_phone LIKE :q3)';
            $params['q'] = $params['q2'] = $params['q3'] = $this->likeTerm($search);
        }
        if (preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            $clauses[] = "DATE_FORMAT(o.created_at, '%Y-%m') = :month";
            $params['month'] = $month;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    public function reportCount(string $search, string $month): int
    {
        [$where, $params] = $this->reportWhere($search, $month);

        return (int) $this->fetchValue("SELECT COUNT(*) FROM orders o {$where}", $params);
    }

    /** @return array<int, array<string, mixed>> */
    public function reportList(string $search, string $month, int $limit, int $offset): array
    {
        [$where, $params] = $this->reportWhere($search, $month);
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            "SELECT o.* FROM orders o {$where} ORDER BY o.created_at ASC, o.id ASC LIMIT :limit OFFSET :offset",
            $params
        );
    }

    /** All matching rows, no pagination — used for the print/export view. */
    public function reportAll(string $search, string $month): array
    {
        [$where, $params] = $this->reportWhere($search, $month);

        return $this->fetchAll("SELECT o.* FROM orders o {$where} ORDER BY o.created_at ASC, o.id ASC", $params);
    }

    /** @return array{total_orders: int, delivered_orders: int, total_revenue: int} */
    public function reportSummary(string $search, string $month): array
    {
        [$where, $params] = $this->reportWhere($search, $month);
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS total_orders,
                    SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders,
                    COALESCE(SUM(o.total_amount), 0) AS total_revenue
             FROM orders o {$where}",
            $params
        ) ?? ['total_orders' => 0, 'delivered_orders' => 0, 'total_revenue' => 0];

        return [
            'total_orders' => (int) $row['total_orders'],
            'delivered_orders' => (int) $row['delivered_orders'],
            'total_revenue' => (int) $row['total_revenue'],
        ];
    }
}
