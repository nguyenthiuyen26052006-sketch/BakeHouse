<?php

declare(strict_types=1);

use App\Controllers\Client\AccountController;
use App\Controllers\Client\AuthController;
use App\Controllers\Client\CartController;
use App\Controllers\Client\CheckoutController;
use App\Controllers\Client\ContactController;
use App\Controllers\Client\HomeController;
use App\Controllers\Client\OrderController;
use App\Controllers\Client\PageController;
use App\Controllers\Client\ProductController;
use App\Controllers\Client\ReviewController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\NotAdminMiddleware;
use App\Middleware\GuestMiddleware;

/** @var Router $router */
$router->group('', [NotAdminMiddleware::class], static function (Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/about', [PageController::class, 'about']);
    $router->get('/faq', [PageController::class, 'faq']);
    $router->get('/privacy', [PageController::class, 'privacy']);
    $router->get('/terms', [PageController::class, 'terms']);
    $router->get('/gallery', [PageController::class, 'gallery']);
    $router->get('/blog', [PageController::class, 'blog']);
    $router->get('/contact', [PageController::class, 'contact']);
    $router->post('/contact', [PageController::class, 'sendContact']);

    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/products/{slug}', [ProductController::class, 'show']);

    // Guests only
    $router->get('/login', [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
    $router->post('/login', [AuthController::class, 'login'], [GuestMiddleware::class]);
    $router->get('/register', [AuthController::class, 'showRegister'], [GuestMiddleware::class]);
    $router->post('/register', [AuthController::class, 'register'], [GuestMiddleware::class]);
    $router->get('/forgot-password', [AuthController::class, 'showForgot'], [GuestMiddleware::class]);
    $router->post('/forgot-password', [AuthController::class, 'forgot'], [GuestMiddleware::class]);
    $router->post('/forgot-password/reset', [AuthController::class, 'resetDirect'], [GuestMiddleware::class]);

    // Authenticated users
    $router->group('', [AuthMiddleware::class], static function (Router $router): void {
        $router->post('/logout', [AuthController::class, 'logout']);
        $router->get('/account', [AccountController::class, 'show']);
        $router->post('/account', [AccountController::class, 'update']);

        $router->get('/cart', [CartController::class, 'index']);
        $router->get('/checkout', [CheckoutController::class, 'show']);
        $router->post('/checkout', [CheckoutController::class, 'store']);
        $router->get('/orders', [OrderController::class, 'index']);
        $router->get('/orders/{code}', [OrderController::class, 'show']);
        $router->get('/contacts', [ContactController::class, 'index']);

        $router->post('/products/{slug}/review', [ReviewController::class, 'save']);
        $router->post('/products/{slug}/review/delete', [ReviewController::class, 'delete']);
    });
});
