<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\BannerController;
use App\Controllers\Admin\BrandController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\ContactController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrderController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\ReviewController;
use App\Controllers\Admin\TagController;
use App\Controllers\Admin\UserController;
use App\Core\Router;
use App\Middleware\AdminMiddleware;

/** @var Router $router */
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

$router->group('', [AdminMiddleware::class], static function (Router $router): void {
    $router->post('/logout', [AuthController::class, 'logout']);
    $router->get('/', [DashboardController::class, 'index']);

    // Products (multi-vendor: write actions are guarded by ProductPolicy in the controller)
    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/products/create', [ProductController::class, 'create']);
    $router->post('/products', [ProductController::class, 'store']);
    $router->get('/products/{id}', [ProductController::class, 'show']);
    $router->get('/products/{id}/edit', [ProductController::class, 'edit']);
    $router->post('/products/{id}', [ProductController::class, 'update']);
    $router->post('/products/{id}/quick', [ProductController::class, 'quickUpdate']);
    $router->post('/products/{id}/delete', [ProductController::class, 'destroy']);

    // Shared lookup resources
    $resources = [
        'categories' => CategoryController::class,
        'brands' => BrandController::class,
        'tags' => TagController::class,
        'banners' => BannerController::class,
    ];
    foreach ($resources as $path => $controller) {
        $router->get("/{$path}", [$controller, 'index']);
        $router->get("/{$path}/create", [$controller, 'create']);
        $router->post("/{$path}", [$controller, 'store']);
        $router->get("/{$path}/{id}/edit", [$controller, 'edit']);
        $router->post("/{$path}/{id}", [$controller, 'update']);
        $router->post("/{$path}/{id}/delete", [$controller, 'destroy']);
    }

    $router->get('/users', [UserController::class, 'index']);
    $router->get('/users/{id}', [UserController::class, 'show']);
    $router->post('/users/{id}/role', [UserController::class, 'updateRole']);
    $router->post('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);

    $router->get('/orders', [OrderController::class, 'index']);
    $router->get('/orders/{id}', [OrderController::class, 'show']);
    $router->post('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    $router->post('/orders/{id}/confirm-payment', [OrderController::class, 'confirmPayment']);

    $router->get('/reviews', [ReviewController::class, 'index']);
    $router->post('/reviews/{id}/toggle', [ReviewController::class, 'toggle']);
    $router->post('/reviews/{id}/delete', [ReviewController::class, 'destroy']);

    $router->get('/contacts', [ContactController::class, 'index']);
    $router->post('/contacts/{id}/status', [ContactController::class, 'updateStatus']);
});
