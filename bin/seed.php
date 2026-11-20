<?php

declare(strict_types=1);

// Usage: php bin/seed.php [--no-images] [--no-embed]
// Dựng lại toàn bộ CSDL: xoá bảng cũ -> 001_schema -> 002_seed -> 003_chatbot,
// rồi tải ảnh seed lên Azure Blob (hoặc public/uploads nếu chưa cấu hình Azure)
// và tạo embedding cho chatbot (nếu đã cấu hình AZURE_OPENAI_*).

use App\Core\Database;
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

$pdo = Database::connection();

echo "Xoá bảng cũ…\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $pdo->exec('DROP TABLE IF EXISTS `' . $table . '`');
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

foreach (['001_schema.sql', '002_seed.sql', '003_chatbot.sql'] as $file) {
    echo "Chạy {$file}…\n";
    $stmt = $pdo->query((string) file_get_contents(BASE_PATH . '/database/init/' . $file));
    do {
        // nextRowset() surfaces errors of later statements in a multi-statement script
    } while ($stmt->nextRowset());
}

foreach (['users', 'categories', 'brands', 'products', 'product_images', 'orders', 'order_items', 'reviews', 'contacts'] as $table) {
    printf("  %-15s %d\n", $table, (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn());
}

if (!in_array('--no-images', $argv, true)) {
    echo "Tải ảnh seed…\n";
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/upload-seed-images.php'));
}

if (!in_array('--no-embed', $argv, true)) {
    if (Env::get('AZURE_OPENAI_API_KEY', '') === '') {
        echo "Bỏ qua embedding (chưa cấu hình AZURE_OPENAI_*). Chatbot sẽ tự tạo khi cần.\n";
    } else {
        echo "Tạo embedding cho chatbot…\n";
        echo (new ProductEmbeddingIndexer())->sync() . " sản phẩm.\n";
    }
}

echo "Xong.\n";
