<?php

declare(strict_types=1);

namespace App\Repositories;

final class CategoryRepository extends BaseRepository
{
    /** @return array<int, array<string, mixed>> */
    public function activeWithCounts(): array
    {
        return $this->fetchAll(
            'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = 1) AS product_count
             FROM categories c WHERE c.is_active = 1 ORDER BY c.id'
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function allForSelect(): array
    {
        return $this->fetchAll('SELECT id, name FROM categories ORDER BY name');
    }
}
