<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class ProductRepository extends BaseRepository
{
    private const SORTS = [
        'newest' => 'p.created_at DESC, p.id DESC',
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'best_selling' => 'sold DESC, p.id DESC',
        'top_rated' => 'p.rating_avg DESC, p.rating_count DESC',
    ];

    private const LIST_SELECT = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug, b.name AS brand_name,
            COALESCE((SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.product_id = p.id AND oi.status = \'delivered\'), 0) AS sold
        FROM products p
        JOIN categories c ON c.id = p.category_id
        LEFT JOIN brands b ON b.id = p.brand_id';

    public function countActive(): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM products WHERE is_active = 1');
    }

    public static function sortKeys(): array
    {
        return array_keys(self::SORTS);
    }

    /** Add display flags (is_new / is_best) used by the product card. */
    private function decorate(array $rows): array
    {
        $newSince = strtotime('-30 days');
        foreach ($rows as &$row) {
            $row['is_new'] = strtotime((string) $row['created_at']) >= $newSince;
            $row['is_best'] = (int) ($row['sold'] ?? 0) >= 2;
        }

        return $rows;
    }

    /**
     * @param array{q?: string, category?: string, max_price?: int, min_price?: int} $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function publicWhere(array $filters): array
    {
        $clauses = ['p.is_active = 1', 'c.is_active = 1'];
        $params = [];
        if (($filters['q'] ?? '') !== '') {
            $clauses[] = '(p.name LIKE :q OR b.name LIKE :q2)';
            $params['q'] = $params['q2'] = $this->likeTerm($filters['q']);
        }
        if (($filters['category'] ?? '') !== '') {
            $clauses[] = 'c.slug = :category';
            $params['category'] = $filters['category'];
        }
        if (($filters['min_price'] ?? 0) > 0) {
            $clauses[] = 'p.price >= :min_price';
            $params['min_price'] = (int) $filters['min_price'];
        }
        if (($filters['max_price'] ?? 0) > 0) {
            $clauses[] = 'p.price <= :max_price';
            $params['max_price'] = (int) $filters['max_price'];
        }

        return ['WHERE ' . implode(' AND ', $clauses), $params];
    }

    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->publicWhere($filters);

        return (int) $this->fetchValue(
            "SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id {$where}",
            $params
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function search(array $filters, string $sort, int $limit, int $offset): array
    {
        [$where, $params] = $this->publicWhere($filters);
        $order = self::SORTS[$sort] ?? self::SORTS['newest'];
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->decorate($this->fetchAll(
            self::LIST_SELECT . " {$where} ORDER BY {$order} LIMIT :limit OFFSET :offset",
            $params
        ));
    }

    public function maxPrice(): int
    {
        return (int) $this->fetchValue('SELECT COALESCE(MAX(price), 0) FROM products WHERE is_active = 1');
    }

    /** @return array<int, array<string, mixed>> */
    public function featured(int $limit): array
    {
        return $this->decorate($this->fetchAll(
            self::LIST_SELECT . ' WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.id DESC LIMIT :limit',
            ['limit' => $limit]
        ));
    }

    /** @return array<int, array<string, mixed>> */
    public function newest(int $limit): array
    {
        return $this->search([], 'newest', $limit, 0);
    }

    /** @return array<int, array<string, mixed>> */
    public function bestSelling(int $limit): array
    {
        return $this->search([], 'best_selling', $limit, 0);
    }

    /** @return array<int, array<string, mixed>> */
    public function topRated(int $limit): array
    {
        return $this->decorate($this->fetchAll(
            self::LIST_SELECT . ' WHERE p.is_active = 1 AND p.rating_count > 0
             ORDER BY p.rating_avg DESC, p.rating_count DESC LIMIT :limit',
            ['limit' => $limit]
        ));
    }

    public function findBySlug(string $slug): ?array
    {
        $row = $this->fetchOne(
            self::LIST_SELECT . ' WHERE p.slug = ? AND p.is_active = 1 AND c.is_active = 1',
            [$slug]
        );

        return $row === null ? null : $this->decorate([$row])[0];
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(self::LIST_SELECT . ' WHERE p.id = ?', [$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function related(int $productId, int $categoryId, int $limit): array
    {
        return $this->decorate($this->fetchAll(
            self::LIST_SELECT . ' WHERE p.is_active = 1 AND p.category_id = :cat AND p.id <> :id
             ORDER BY p.rating_avg DESC, p.id DESC LIMIT :limit',
            ['cat' => $categoryId, 'id' => $productId, 'limit' => $limit]
        ));
    }

    /** @return array<int, array<string, mixed>> */
    public function images(int $productId): array
    {
        return $this->fetchAll(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id',
            [$productId]
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function tags(int $productId): array
    {
        return $this->fetchAll(
            'SELECT t.* FROM tags t JOIN product_tags pt ON pt.tag_id = t.id WHERE pt.product_id = ? ORDER BY t.name',
            [$productId]
        );
    }

    /** @return array<int, int> */
    public function tagIds(int $productId): array
    {
        return array_map('intval', array_column($this->tags($productId), 'id'));
    }

    /** @return array<int, array<string, mixed>> products by ids that are active (used by chatbot/checkout) */
    public function findActiveByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        return $this->fetchAll(
            "SELECT * FROM products WHERE is_active = 1 AND id IN ({$placeholders})",
            array_values(array_map('intval', $ids))
        );
    }

    /**
     * Keyword search for the chatbot: any of the keywords in name/description/category/tag.
     *
     * @param array<int, string> $keywords
     * @return array<int, array<string, mixed>>
     */
    public function searchByKeywords(array $keywords, int $limit): array
    {
        if ($keywords === []) {
            return [];
        }
        $clauses = [];
        $params = [];
        foreach (array_values($keywords) as $i => $keyword) {
            $clauses[] = "(p.name LIKE :k{$i} OR p.description LIKE :d{$i} OR c.name LIKE :c{$i})";
            $params["k{$i}"] = $params["d{$i}"] = $params["c{$i}"] = $this->likeTerm($keyword);
        }
        $params['limit'] = $limit;

        return $this->decorate($this->fetchAll(
            self::LIST_SELECT . ' WHERE p.is_active = 1 AND p.stock_quantity > 0 AND (' . implode(' OR ', $clauses) . ')
             ORDER BY p.is_featured DESC, p.rating_avg DESC LIMIT :limit',
            $params
        ));
    }

    // ---------------------------------------------------------------- admin

    /** @return array{0: string, 1: array<string, mixed>} */
    private function adminWhere(string $search, int $categoryId, string $status): array
    {
        $clauses = [];
        $params = [];
        if ($search !== '') {
            $clauses[] = '(p.name LIKE :q OR b.name LIKE :q2)';
            $params['q'] = $params['q2'] = $this->likeTerm($search);
        }
        if ($categoryId > 0) {
            $clauses[] = 'p.category_id = :cat';
            $params['cat'] = $categoryId;
        }
        if ($status === 'active' || $status === 'hidden') {
            $clauses[] = 'p.is_active = :active';
            $params['active'] = $status === 'active' ? 1 : 0;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    public function adminCount(string $search, int $categoryId, string $status): int
    {
        [$where, $params] = $this->adminWhere($search, $categoryId, $status);

        return (int) $this->fetchValue(
            "SELECT COUNT(*) FROM products p LEFT JOIN brands b ON b.id = p.brand_id {$where}",
            $params
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function adminList(string $search, int $categoryId, string $status, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminWhere($search, $categoryId, $status);
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            "SELECT p.*, c.name AS category_name, b.name AS brand_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             {$where} ORDER BY p.id DESC LIMIT :limit OFFSET :offset",
            $params
        );
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, array $tagIds): int
    {
        return Database::transaction(function () use ($data, $tagIds): int {
            $columns = array_keys($data);
            $this->execute(
                sprintf(
                    'INSERT INTO products (%s) VALUES (%s)',
                    implode(', ', $columns),
                    implode(', ', array_fill(0, count($columns), '?'))
                ),
                array_values($data)
            );
            $id = (int) $this->db()->lastInsertId();
            $this->syncTags($id, $tagIds);

            return $id;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data, array $tagIds): void
    {
        Database::transaction(function () use ($id, $data, $tagIds): void {
            $assignments = implode(', ', array_map(static fn (string $c): string => "{$c} = ?", array_keys($data)));
            $this->execute("UPDATE products SET {$assignments} WHERE id = ?", [...array_values($data), $id]);
            $this->syncTags($id, $tagIds);
        });
    }

    public function updatePriceAndStock(int $id, int $price, int $stock): void
    {
        $this->execute('UPDATE products SET price = ?, stock_quantity = ? WHERE id = ?', [$price, $stock, $id]);
    }

    public function syncTags(int $productId, array $tagIds): void
    {
        $this->execute('DELETE FROM product_tags WHERE product_id = ?', [$productId]);
        foreach (array_unique(array_map('intval', $tagIds)) as $tagId) {
            if ($tagId > 0) {
                $this->execute('INSERT INTO product_tags (product_id, tag_id) VALUES (?, ?)', [$productId, $tagId]);
            }
        }
    }

    public function hasOrders(int $id): bool
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM order_items WHERE product_id = ?', [$id]) > 0;
    }

    public function setActive(int $id, bool $active): void
    {
        $this->execute('UPDATE products SET is_active = ? WHERE id = ?', [(int) $active, $id]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM products WHERE id = ?', [$id]);
    }

    public function addImage(int $productId, string $path): void
    {
        $this->execute('INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, 0)', [$productId, $path]);
    }

    public function findImage(int $imageId, int $productId): ?array
    {
        return $this->fetchOne('SELECT * FROM product_images WHERE id = ? AND product_id = ?', [$imageId, $productId]);
    }

    public function deleteImage(int $imageId): void
    {
        $this->execute('DELETE FROM product_images WHERE id = ?', [$imageId]);
    }

    public function slugFor(string $name, ?int $ignoreId): string
    {
        return $this->uniqueSlug('products', $name, $ignoreId);
    }
}
