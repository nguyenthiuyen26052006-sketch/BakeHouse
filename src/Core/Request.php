<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $files,
        private readonly array $server
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                $method = $override;
            }
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $path = '/' . trim(rawurldecode($path), '/');

        return new self($method, $path, $_GET, $_POST, $_FILES, $_SERVER);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function getAllQuery(): array
    {
        return $this->query;
    }

    public function getInput(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function getAllInput(): array
    {
        return $this->body;
    }

    public function getFile(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        return is_array($file) ? $file : null;
    }

    /**
     * Normalise a multi-file input (name="x[]") into a list of single-file arrays, skipping empty slots.
     *
     * @return array<int, array{name: string, tmp_name: string, error: int, size: int}>
     */
    public function getFiles(string $key): array
    {
        $entry = $this->files[$key] ?? null;
        if (!is_array($entry)) {
            return [];
        }
        if (!is_array($entry['name'])) {
            return $entry['error'] === UPLOAD_ERR_NO_FILE ? [] : [$entry];
        }

        $files = [];
        foreach (array_keys($entry['name']) as $index) {
            if ($entry['error'][$index] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $files[] = [
                'name' => $entry['name'][$index],
                'tmp_name' => $entry['tmp_name'][$index],
                'error' => $entry['error'][$index],
                'size' => $entry['size'][$index],
            ];
        }

        return $files;
    }

    public function getJson(): array
    {
        $decoded = json_decode((string) file_get_contents('php://input'), true);

        return is_array($decoded) ? $decoded : [];
    }

    public function getIp(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function isAjax(): bool
    {
        return strtolower($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || str_contains($this->server['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function getHeader(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));

        return $this->server[$key] ?? null;
    }
}
