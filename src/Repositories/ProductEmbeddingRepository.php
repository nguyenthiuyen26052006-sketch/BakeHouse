<?php

declare(strict_types=1);

namespace App\Repositories;

final class ProductEmbeddingRepository extends BaseRepository
{
    /** @return array<int, string> product_id => content_hash */
    public function hashes(): array
    {
        $map = [];
        foreach ($this->fetchAll('SELECT product_id, content_hash FROM product_embeddings') as $row) {
            $map[(int) $row['product_id']] = (string) $row['content_hash'];
        }

        return $map;
    }

    /** @param array<int, float> $vector */
    public function upsert(int $productId, string $hash, array $vector): void
    {
        $this->execute(
            'INSERT INTO product_embeddings (product_id, content_hash, embedding) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE content_hash = VALUES(content_hash), embedding = VALUES(embedding)',
            [$productId, $hash, json_encode($vector, JSON_THROW_ON_ERROR)]
        );
    }

    /**
     * Vectors of products that can be recommended right now (active, in stock).
     * Price is returned so callers can filter in memory and load the vectors only once per request.
     *
     * @return array<int, array{product_id: int, price: int, embedding: array<int, float>}>
     */
    public function searchable(): array
    {
        return array_map(static fn (array $r): array => [
            'product_id' => (int) $r['product_id'],
            'price' => (int) $r['price'],
            'embedding' => json_decode((string) $r['embedding'], true, 512, JSON_THROW_ON_ERROR),
        ], $this->fetchAll(
            'SELECT e.product_id, p.price, e.embedding FROM product_embeddings e
             JOIN products p ON p.id = e.product_id
             JOIN categories c ON c.id = p.category_id
             WHERE p.is_active = 1 AND c.is_active = 1 AND p.stock_quantity > 0'
        ));
    }

    /** Number of active products that have no embedding yet. */
    public function countMissing(): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM products p
             LEFT JOIN product_embeddings e ON e.product_id = p.id
             WHERE p.is_active = 1 AND e.product_id IS NULL'
        );
    }

    /** Active products with the text pieces used to build their embedding. @return array<int, array<string, mixed>> */
    public function documents(?int $productId = null): array
    {
        return $this->fetchAll(
            'SELECT p.id, p.name, p.description, p.unit, c.name AS category_name, b.name AS brand_name,
                    (SELECT GROUP_CONCAT(t.name SEPARATOR ", ") FROM tags t
                       JOIN product_tags pt ON pt.tag_id = t.id WHERE pt.product_id = p.id) AS tag_names
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.is_active = 1' . ($productId !== null ? ' AND p.id = :pid' : ''),
            $productId !== null ? ['pid' => $productId] : []
        );
    }
}
