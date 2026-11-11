<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Response;

final class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): ?Response
    {
        if (!Auth::check()) {
            return Response::redirect('/admin/login');
        }
        if (!Auth::isAdmin()) {
            return ErrorHandler::render(403, $request);
        }

        return null;
    }
}
