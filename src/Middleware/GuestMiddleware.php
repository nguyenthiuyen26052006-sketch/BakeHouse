<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request): ?Response
    {
        return Auth::check() ? Response::redirect('/') : null;
    }
}
