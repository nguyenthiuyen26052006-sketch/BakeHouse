<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class ReviewRepository extends BaseRepository
{
    public function findByUserAndProduct(int $userId, int $productId): ?array
    {
        return $this->fetchOne('SELECT * FROM reviews WHERE user_id = ? AND product_id = ?', [$userId, $productId]);
    }

    public function hasPurchasedDelivered(int $userId, int $productId): bool
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE o.user_id = ? AND oi.product_id = ? AND oi.status = \'delivered\'',
            [$userId, $productId]
        ) > 0;
    }

    public function countVisible(int $productId): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM reviews WHERE product_id = ? AND is_hidden = 0', [$productId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function listVisible(int $productId, int $limit, int $offset): array
    {
        return $this->fetchAll(
            'SELECT r.*, COALESCE(NULLIF(p.full_name, \'\'), \'Khách hàng\') AS author_name
             FROM reviews r LEFT JOIN user_profiles p ON p.user_id = r.user_id
             WHERE r.product_id = :pid AND r.is_hidden = 0
             ORDER BY r.id DESC LIMIT :limit OFFSET :offset',
            ['pid' => $productId, 'limit' => $limit, 'offset' => $offset]
        );
    }

    public function save(int $userId, int $productId, int $rating, ?string $comment): void
    {
        Database::transaction(function () use ($userId, $productId, $rating, $comment): void {
            $this->execute(
                'INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)',
                [$userId, $productId, $rating, $comment]
            );
            $this->recalculate($productId);
        });
    }

    public function deleteByUserAndProduct(int $userId, int $productId): void
    {
        Database::transaction(function () use ($userId, $productId): void {
            $this->execute('DELETE FROM reviews WHERE user_id = ? AND product_id = ?', [$userId, $productId]);
            $this->recalculate($productId);
        });
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM reviews WHERE id = ?', [$id]);
    }

    public function reply(int $id, ?string $reply): void
    {
        $this->execute(
            'UPDATE reviews SET admin_reply = ?, admin_replied_at = ? WHERE id = ?',
            [$reply, $reply !== null ? date('Y-m-d H:i:s') : null, $id]
        );
    }

    public function deleteById(int $id): void
    {
        $review = $this->find($id);
        if ($review === null) {
            return;
        }
        $this->execute('DELETE FROM reviews WHERE id = ?', [$id]);
        $this->recalculate((int) $review['product_id']);
    }

    /** Refresh products.rating_avg / rating_count from visible reviews. */
    public function recalculate(int $productId): void
    {
        $this->execute(
            'UPDATE products SET
                rating_avg = COALESCE((SELECT ROUND(AVG(rating), 2) FROM reviews WHERE product_id = ? AND is_hidden = 0), 0),
                rating_count = (SELECT COUNT(*) FROM reviews WHERE product_id = ? AND is_hidden = 0)
             WHERE id = ?',
            [$productId, $productId, $productId]
        );
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    /**
     * @param string $view '' | 'unanswered'
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function adminWhere(string $search, string $view, int $rating): array
    {
        $clauses = [];
        $params = [];
        if ($search !== '') {
            $clauses[] = '(pr.name LIKE :q OR r.comment LIKE :q2 OR u.email LIKE :q3)';
            $params['q'] = $params['q2'] = $params['q3'] = $this->likeTerm($search);
        }
        if ($rating >= 1 && $rating <= 5) {
            $clauses[] = 'r.rating = :rating';
            $params['rating'] = $rating;
        }
        if ($view === 'unanswered') {
            $clauses[] = "(r.admin_reply IS NULL OR r.admin_reply = '')";
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    public function adminCount(string $search, string $view, int $rating): int
    {
        [$where, $params] = $this->adminWhere($search, $view, $rating);

        return (int) $this->fetchValue(
            "SELECT COUNT(*) FROM reviews r JOIN products pr ON pr.id = r.product_id JOIN users u ON u.id = r.user_id {$where}",
            $params
        );
    }

    /** Counts for the admin tabs. @return array<string, int> */
    public function viewCounts(): array
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(admin_reply IS NULL OR admin_reply = ''), 0) AS unanswered
             FROM reviews"
        ) ?? [];

        return ['all' => (int) ($row['total'] ?? 0), 'unanswered' => (int) ($row['unanswered'] ?? 0)];
    }

    /** @return array<int, array<string, mixed>> */
    public function adminList(string $search, string $view, int $rating, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminWhere($search, $view, $rating);
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            "SELECT r.*, pr.name AS product_name, pr.slug AS product_slug, pr.image_path AS product_image, u.email AS user_email,
                    COALESCE(NULLIF(p.full_name, ''), u.email) AS author_name
             FROM reviews r
             JOIN products pr ON pr.id = r.product_id
             JOIN users u ON u.id = r.user_id
             LEFT JOIN user_profiles p ON p.user_id = u.id
             {$where} ORDER BY r.id DESC LIMIT :limit OFFSET :offset",
            $params
        );
    }
}
