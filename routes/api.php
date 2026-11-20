<?php

declare(strict_types=1);

use App\Controllers\Api\CartApiController;
use App\Controllers\Api\ChatbotApiController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\NotAdminMiddleware;

/** @var Router $router */
$router->group('', [NotAdminMiddleware::class], static function (Router $router): void {
    $router->post('/chatbot/chat', [ChatbotApiController::class, 'chat']);
    $router->post('/chatbot/stream', [ChatbotApiController::class, 'stream']);

    $router->group('/cart', [AuthMiddleware::class], static function (Router $router): void {
        $router->post('/add', [CartApiController::class, 'add']);
        $router->post('/update', [CartApiController::class, 'update']);
        $router->post('/remove', [CartApiController::class, 'remove']);
    });
});
