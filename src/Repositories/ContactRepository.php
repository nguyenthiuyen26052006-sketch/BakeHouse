<?php

declare(strict_types=1);

namespace App\Repositories;

final class ContactRepository extends BaseRepository
{
    public function create(string $fullName, ?string $email, ?string $phone, string $message, ?int $userId = null): void
    {
        $this->execute(
            'INSERT INTO contacts (user_id, full_name, email, phone, message) VALUES (?, ?, ?, ?, ?)',
            [$userId, $fullName, $email, $phone, $message]
        );
    }

    public function countByUser(int $userId): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM contacts WHERE user_id = ?', [$userId]);
    }

    /** @return array<int, array<string, mixed>> */
    public function listByUser(int $userId, int $limit, int $offset): array
    {
        return $this->fetchAll(
            'SELECT * FROM contacts WHERE user_id = :uid ORDER BY id DESC LIMIT :limit OFFSET :offset',
            ['uid' => $userId, 'limit' => $limit, 'offset' => $offset]
        );
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function where(string $search, string $status): array
    {
        $clauses = [];
        $params = [];
        if ($search !== '') {
            $clauses[] = '(full_name LIKE :q OR email LIKE :q2 OR phone LIKE :q3 OR message LIKE :q4)';
            $params['q'] = $params['q2'] = $params['q3'] = $params['q4'] = $this->likeTerm($search);
        }
        if (in_array($status, ['new', 'read', 'replied'], true)) {
            $clauses[] = 'status = :status';
            $params['status'] = $status;
        }

        return [$clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses), $params];
    }

    public function adminCount(string $search, string $status): int
    {
        [$where, $params] = $this->where($search, $status);

        return (int) $this->fetchValue("SELECT COUNT(*) FROM contacts {$where}", $params);
    }

    /** @return array<int, array<string, mixed>> */
    public function adminList(string $search, string $status, int $limit, int $offset): array
    {
        [$where, $params] = $this->where($search, $status);
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            "SELECT * FROM contacts {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset",
            $params
        );
    }

    public function setStatus(int $id, string $status): void
    {
        $this->execute('UPDATE contacts SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function reply(int $id, ?string $reply): void
    {
        $this->execute(
            'UPDATE contacts SET admin_reply = ?, admin_replied_at = ?, status = IF(? IS NOT NULL, \'replied\', status) WHERE id = ?',
            [$reply, $reply !== null ? date('Y-m-d H:i:s') : null, $reply, $id]
        );
    }
}
