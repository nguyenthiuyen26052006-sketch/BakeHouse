<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Generic CRUD for simple lookup tables (categories, brands, tags, banners).
 * $table and column names come from controller config, never from user input.
 */
final class SimpleTableRepository extends BaseRepository
{
    /** @param array<int, string> $searchColumns */
    public function __construct(private readonly string $table, private readonly array $searchColumns)
    {
    }

    public function countAll(string $search = ''): int
    {
        [$where, $params] = $this->buildWhere($search);

        return (int) $this->fetchValue("SELECT COUNT(*) FROM `{$this->table}` {$where}", $params);
    }

    /** @return array<int, array<string, mixed>> */
    public function paginate(string $search, int $limit, int $offset): array
    {
        [$where, $params] = $this->buildWhere($search);
        $params['limit'] = $limit;
        $params['offset'] = $offset;

        return $this->fetchAll(
            "SELECT * FROM `{$this->table}` {$where} ORDER BY id DESC LIMIT :limit OFFSET :offset",
            $params
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM `{$this->table}` WHERE id = ?", [$id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function all(string $orderBy = 'name'): array
    {
        $orderBy = in_array($orderBy, ['name', 'id', 'title'], true) ? $orderBy : 'id';

        return $this->fetchAll("SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}`");
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(', ', array_map(static fn (string $c): string => "`{$c}`", $columns)),
            implode(', ', array_fill(0, count($columns), '?'))
        );
        $this->execute($sql, array_values($data));

        return (int) $this->db()->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        $assignments = implode(', ', array_map(static fn (string $c): string => "`{$c}` = ?", array_keys($data)));
        $this->execute("UPDATE `{$this->table}` SET {$assignments} WHERE id = ?", [...array_values($data), $id]);
    }

    public function delete(int $id): void
    {
        $this->execute("DELETE FROM `{$this->table}` WHERE id = ?", [$id]);
    }

    public function slugFor(string $source, ?int $ignoreId): string
    {
        return $this->uniqueSlug($this->table, $source, $ignoreId);
    }

    /** @return array{0: string, 1: array<string, string>} */
    private function buildWhere(string $search): array
    {
        if ($search === '' || $this->searchColumns === []) {
            return ['', []];
        }
        $clauses = [];
        $params = [];
        foreach ($this->searchColumns as $index => $column) {
            $clauses[] = "`{$column}` LIKE :s{$index}";
            $params["s{$index}"] = $this->likeTerm($search);
        }

        return ['WHERE ' . implode(' OR ', $clauses), $params];
    }
}
