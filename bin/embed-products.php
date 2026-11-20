<?php

declare(strict_types=1);

// Usage: php bin/embed-products.php  — (re)builds product embeddings for the chatbot.

use App\Core\Env;
use App\Services\Chatbot\ProductEmbeddingIndexer;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
require_once BASE_PATH . '/src/Helpers/functions.php';
Env::load(BASE_PATH . '/.env');

$count = (new ProductEmbeddingIndexer())->sync();
echo "Đã cập nhật embedding cho {$count} sản phẩm.\n";
