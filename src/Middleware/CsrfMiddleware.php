<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

final class CsrfMiddleware implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(Request $request): ?Response
    {
        if (in_array($request->getMethod(), self::SAFE_METHODS, true)) {
            return null;
        }

        $token = $request->getInput('_token') ?? $request->getHeader('X-CSRF-TOKEN');
        if (Csrf::verify(is_string($token) ? $token : null)) {
            return null;
        }

        if ($request->isAjax()) {
            return Response::json(['message' => 'Phiên làm việc đã hết hạn.'], 419);
        }

        return Response::html(\App\Core\View::render('errors.419', [], 'layouts.main'), 419);
    }
}
