<?php

declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\BrandController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrderController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\ReportController;
use App\Controllers\Admin\ReviewController;
use App\Core\Router;
use App\Middleware\AdminMiddleware;

/** @var Router $router */
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

$router->group('', [AdminMiddleware::class], static function (Router $router): void {
    $router->post('/logout', [AuthController::class, 'logout']);
    $router->get('/', [DashboardController::class, 'index']);
    $router->get('/report', [ReportController::class, 'print']);

    // Products (write actions are guarded by ProductPolicy in the controller)
    $router->get('/products', [ProductController::class, 'index']);
    $router->get('/products/create', [ProductController::class, 'create']);
    $router->post('/products', [ProductController::class, 'store']);
    $router->get('/products/{id}/edit', [ProductController::class, 'edit']);
    $router->post('/products/{id}', [ProductController::class, 'update']);
    $router->post('/products/{id}/quick', [ProductController::class, 'quickUpdate']);
    $router->post('/products/{id}/delete', [ProductController::class, 'destroy']);

    // Product classification
    $resources = [
        'categories' => CategoryController::class,
        'brands' => BrandController::class,
    ];
    foreach ($resources as $path => $controller) {
        $router->get("/{$path}", [$controller, 'index']);
        $router->get("/{$path}/create", [$controller, 'create']);
        $router->post("/{$path}", [$controller, 'store']);
        $router->get("/{$path}/{id}/edit", [$controller, 'edit']);
        $router->post("/{$path}/{id}", [$controller, 'update']);
        $router->post("/{$path}/{id}/delete", [$controller, 'destroy']);
    }

    // Orders / fulfilment
    $router->get('/orders', [OrderController::class, 'index']);
    $router->get('/orders/{id}', [OrderController::class, 'show']);
    $router->post('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    $router->post('/orders/{id}/confirm-payment', [OrderController::class, 'confirmPayment']);

    // Customer reviews
    $router->get('/reviews', [ReviewController::class, 'index']);
    $router->post('/reviews/{id}/reply', [ReviewController::class, 'reply']);
    $router->post('/reviews/{id}/delete', [ReviewController::class, 'destroy']);
});
