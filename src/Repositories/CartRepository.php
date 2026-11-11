<?php

declare(strict_types=1);

namespace App\Repositories;

final class CartRepository extends BaseRepository
{
    public function getOrCreateCartId(int $userId): int
    {
        $id = $this->fetchValue('SELECT id FROM carts WHERE user_id = ?', [$userId]);
        if ($id !== false && $id !== null) {
            return (int) $id;
        }
        $this->execute('INSERT INTO carts (user_id) VALUES (?)', [$userId]);

        return (int) $this->db()->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function items(int $cartId): array
    {
        return $this->fetchAll(
            'SELECT ci.id, ci.product_id, ci.quantity, p.name, p.slug, p.price, p.stock_quantity,
                    p.image_path, p.unit, p.is_active
             FROM cart_items ci JOIN products p ON p.id = ci.product_id
             WHERE ci.cart_id = ? ORDER BY ci.id DESC',
            [$cartId]
        );
    }

    public function countItems(int $userId): int
    {
        return (int) $this->fetchValue(
            'SELECT COALESCE(SUM(ci.quantity), 0) FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ?',
            [$userId]
        );
    }

    public function findItem(int $cartId, int $productId): ?array
    {
        return $this->fetchOne('SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cartId, $productId]);
    }

    public function setQuantity(int $cartId, int $productId, int $quantity): void
    {
        $this->execute(
            'INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)',
            [$cartId, $productId, $quantity]
        );
    }

    public function removeItem(int $cartId, int $productId): void
    {
        $this->execute('DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?', [$cartId, $productId]);
    }

    public function clear(int $cartId): void
    {
        $this->execute('DELETE FROM cart_items WHERE cart_id = ?', [$cartId]);
    }
}
