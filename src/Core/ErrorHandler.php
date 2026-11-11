<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ErrorHandler
{
    private const TITLES = [
        403 => 'Không có quyền truy cập',
        404 => 'Không tìm thấy trang',
        405 => 'Phương thức không được hỗ trợ',
        419 => 'Phiên làm việc đã hết hạn',
        500 => 'Lỗi máy chủ',
    ];

    public static function render(int $status, ?Request $request = null, ?Throwable $exception = null): Response
    {
        if ($request !== null && $request->isAjax()) {
            return Response::json(['message' => self::TITLES[$status] ?? 'Lỗi'], $status);
        }

        $view = is_file(BASE_PATH . "/src/Views/errors/{$status}.php") ? "errors.{$status}" : 'errors.404';
        $html = View::render($view, [
            'status' => $status,
            'title' => self::TITLES[$status] ?? 'Lỗi',
            'exception' => Env::bool('APP_DEBUG') ? $exception : null,
        ]);

        return Response::html($html, $status);
    }

    public static function register(): void
    {
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity) || in_array($severity, [E_DEPRECATED, E_USER_DEPRECATED, E_NOTICE, E_USER_NOTICE], true)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }

    public static function logException(Throwable $e): void
    {
        $line = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        @file_put_contents(BASE_PATH . '/storage/logs/app.log', $line, FILE_APPEND);
    }
}
