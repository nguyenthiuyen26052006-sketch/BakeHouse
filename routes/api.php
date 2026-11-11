<?php

declare(strict_types=1);

use App\Controllers\Api\CartApiController;
use App\Controllers\Api\ChatbotApiController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;

/** @var Router $router */
$router->post('/chatbot/suggest', [ChatbotApiController::class, 'suggest']);

$router->group('/cart', [AuthMiddleware::class], static function (Router $router): void {
    $router->post('/add', [CartApiController::class, 'add']);
    $router->post('/update', [CartApiController::class, 'update']);
    $router->post('/remove', [CartApiController::class, 'remove']);
});
