<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Keeps the storefront (public pages, cart, checkout, orders, account, chatbot) for visitors and customers only.
 * An administrator works in /admin; one browser session holds one role, so switching means logging out first.
 */
final class NotAdminMiddleware implements MiddlewareInterface
{
    public const MESSAGE = 'Tài khoản quản trị không dùng website khách. Hãy đăng xuất nếu muốn mua hàng với tư cách khách.';

    public function handle(Request $request): ?Response
    {
        if (!Auth::isAdmin()) {
            return null;
        }

        if ($request->isAjax()) {
            return Response::json(['ok' => false, 'message' => self::MESSAGE, 'redirect' => '/admin'], 403);
        }

        Session::flash('warning', self::MESSAGE);

        return Response::redirect('/admin');
    }
}
