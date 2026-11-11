<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): ?Response
    {
        if (Auth::check()) {
            return null;
        }

        if ($request->isAjax()) {
            return Response::json(['ok' => false, 'message' => 'Vui lòng đăng nhập.', 'login_url' => '/login'], 401);
        }

        Session::set('intended_url', $_SERVER['REQUEST_URI'] ?? $request->getPath());
        Session::flash('warning', 'Vui lòng đăng nhập để tiếp tục.');

        return Response::redirect('/login');
    }
}
