<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\Slugger;
use PDO;

abstract class BaseRepository
{
    protected function db(): PDO
    {
        return Database::connection();
    }

    /** @return array<int, array<string, mixed>> */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db()->prepare($sql);
        $this->bindAll($stmt, $params);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db()->prepare($sql);
        $this->bindAll($stmt, $params);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    protected function fetchValue(string $sql, array $params = []): mixed
    {
        $stmt = $this->db()->prepare($sql);
        $this->bindAll($stmt, $params);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db()->prepare($sql);
        $this->bindAll($stmt, $params);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /** Bind named/positional params, treating PHP ints as PDO ints (needed for LIMIT/OFFSET). */
    private function bindAll(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $name = is_int($key) ? $key + 1 : (str_starts_with((string) $key, ':') ? $key : ':' . $key);
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $stmt->bindValue($name, $value, $type);
        }
    }

    /** Generate a slug unique inside $table (optionally ignoring a row id). */
    public function uniqueSlug(string $table, string $source, ?int $ignoreId = null): string
    {
        $base = Slugger::slugify($source);
        $slug = $base;
        $suffix = 2;
        while ((int) $this->fetchValue(
            "SELECT COUNT(*) FROM `{$table}` WHERE slug = ? AND id <> ?",
            [$slug, $ignoreId ?? 0]
        ) > 0) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    protected function likeTerm(string $term): string
    {
        return '%' . addcslashes($term, '%_\\') . '%';
    }
}
