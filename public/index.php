<?php

declare(strict_types=1);

use App\Core\Env;
use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Middleware\CsrfMiddleware;

define('BASE_PATH', dirname(__DIR__));

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class): void {
        if (!str_starts_with($class, 'App\\')) {
            return;
        }
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}
require_once BASE_PATH . '/src/Helpers/functions.php';

Env::load(BASE_PATH . '/.env');

ErrorHandler::register();
$request = Request::capture();

try {
    Session::start();

    $router = new Router();
    $router->group('', [CsrfMiddleware::class], static function (Router $router): void {
        require BASE_PATH . '/routes/web.php';
        $router->group('/admin', [], static function (Router $router): void {
            require BASE_PATH . '/routes/admin.php';
        });
        $router->group('/api', [], static function (Router $router): void {
            require BASE_PATH . '/routes/api.php';
        });
    });

    $router->dispatch($request)->send();
} catch (Throwable $e) {
    ErrorHandler::logException($e);
    ErrorHandler::render(500, $request, $e)->send();
}
