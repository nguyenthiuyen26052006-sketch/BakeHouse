<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * Render a view (dot notation, e.g. "client.home") inside an optional layout.
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts.main'): string
    {
        $content = self::capture($view, $data);

        if ($layout === null) {
            return $content;
        }

        return self::capture($layout, array_merge($data, ['content' => $content]));
    }

    private static function capture(string $view, array $data): string
    {
        $file = BASE_PATH . '/src/Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$view}");
        }

        extract(array_merge(self::$shared, $data), EXTR_SKIP);
        ob_start();
        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
