<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class UserRepository extends BaseRepository
{
    private const SELECT = 'SELECT u.*, p.full_name, p.shipping_phone, p.shipping_address, p.avatar_path
        FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id';

    public function findById(int $id): ?array
    {
        return $this->fetchOne(self::SELECT . ' WHERE u.id = ?', [$id]);
    }

    public function findByIdentity(string $identity): ?array
    {
        return $this->fetchOne(self::SELECT . ' WHERE u.email = ? OR u.phone = ? LIMIT 1', [$identity, $identity]);
    }

    public function emailExists(string $email): bool
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM users WHERE email = ?', [$email]) > 0;
    }

    public function phoneExists(string $phone, ?int $ignoreUserId = null): bool
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM users WHERE phone = ? AND id <> ?',
            [$phone, $ignoreUserId ?? 0]
        ) > 0;
    }

    /** Creates user + profile + cart atomically and returns the new user id. */
    public function createWithProfile(?string $email, ?string $phone, string $passwordHash, string $fullName): int
    {
        return Database::transaction(function () use ($email, $phone, $passwordHash, $fullName): int {
            $this->execute(
                'INSERT INTO users (email, phone, password_hash, role) VALUES (?, ?, ?, \'user\')',
                [$email, $phone, $passwordHash]
            );
            $userId = (int) $this->db()->lastInsertId();
            $this->execute(
                'INSERT INTO user_profiles (user_id, full_name, shipping_phone) VALUES (?, ?, ?)',
                [$userId, $fullName, $phone]
            );
            $this->execute('INSERT INTO carts (user_id) VALUES (?)', [$userId]);

            return $userId;
        });
    }

    public function updateProfile(int $userId, string $fullName, ?string $phone, ?string $address): void
    {
        $this->execute(
            'INSERT INTO user_profiles (user_id, full_name, shipping_phone, shipping_address) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE full_name = VALUES(full_name),
                shipping_phone = VALUES(shipping_phone), shipping_address = VALUES(shipping_address)',
            [$userId, $fullName, $phone, $address]
        );
    }

    public function updateAvatar(int $userId, string $path): void
    {
        $this->execute('UPDATE user_profiles SET avatar_path = ? WHERE user_id = ?', [$path, $userId]);
    }

    public function updatePassword(int $userId, string $hash): void
    {
        $this->execute('UPDATE users SET password_hash = ? WHERE id = ?', [$hash, $userId]);
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function adminWhere(string $search, string $role, string $active): array
    {
        $clauses = [];
        $params = [];
        if ($search !== '') {
            $clauses[] = '(u.email LIKE :q OR u.phone LIKE :q2 OR p.full_name LIKE :q3)';
            $params['q'] = $params['q2'] = $params['q3'] = $this->likeTerm($search);
        }
        if (in_array($role, ['user', 'admin'], true)) {
            $clauses[] = 'u.role = :role';
            $params['role'] = $role;
        }
        if ($active === '1' || $active === '0') {
            $clauses[] = 'u.is_active = :active';
            $params['active'] = (int) $active;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    public function adminCount(string $search, string $role, string $active): int
    {
        [$where, $params] = $this->adminWhere($search, $role, $active);

        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id ' . $where,
            $params
        );
    }

    /** @return array<int, array<string, mixed>> */
    public function adminList(string $search, string $role, string $active, int $limit, int $offset): array
    {
        [$where, $params] = $this->adminWhere($search, $role, $active);
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            self::SELECT . " {$where} ORDER BY u.id DESC LIMIT :limit OFFSET :offset",
            $params
        );
    }

    public function setRole(int $userId, string $role): void
    {
        $this->execute('UPDATE users SET role = ? WHERE id = ?', [$role, $userId]);
    }

    public function setActive(int $userId, bool $active): void
    {
        $this->execute('UPDATE users SET is_active = ? WHERE id = ?', [(int) $active, $userId]);
    }

    /** @return array{orders: int, spent: int, reviews: int} */
    public function activitySummary(int $userId): array
    {
        return [
            'orders' => (int) $this->fetchValue('SELECT COUNT(*) FROM orders WHERE user_id = ?', [$userId]),
            'spent' => (int) $this->fetchValue(
                'SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = ? AND status = \'delivered\'',
                [$userId]
            ),
            'reviews' => (int) $this->fetchValue('SELECT COUNT(*) FROM reviews WHERE user_id = ?', [$userId]),
        ];
    }
}
