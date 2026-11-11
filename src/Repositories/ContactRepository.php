<?php

declare(strict_types=1);

namespace App\Repositories;

final class ContactRepository extends BaseRepository
{
    public function create(string $fullName, ?string $email, ?string $phone, string $message): void
    {
        $this->execute(
            'INSERT INTO contacts (full_name, email, phone, message) VALUES (?, ?, ?, ?)',
            [$fullName, $email, $phone, $message]
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
}
