<?php

declare(strict_types=1);

// Usage: php bin/upload-seed-images.php
// Uploads database/seed-images/*.jpg to Azure Blob Storage as uploads/products/seed/<name>.jpg
// (the paths referenced by database/init/002_seed.sql). Re-runnable.
// Without Azure config the files are copied to public/uploads/products/seed/ so the app still shows them.

use App\Core\Env;
use App\Services\Storage\AzureBlobStorage;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
Env::load(BASE_PATH . '/.env');

$files = glob(BASE_PATH . '/database/seed-images/*.jpg') ?: [];

if (!AzureBlobStorage::isConfigured()) {
    $dir = BASE_PATH . '/public/uploads/products/seed';
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fwrite(STDERR, "Không tạo được {$dir}\n");
        exit(1);
    }
    foreach ($files as $file) {
        copy($file, $dir . '/' . basename($file));
    }
    echo count($files) . " ảnh đã chép vào public/uploads/products/seed (chưa cấu hình Azure Storage).\n";
    exit(0);
}

$storage = new AzureBlobStorage();
foreach ($files as $file) {
    $blob = 'uploads/products/seed/' . basename($file);
    $storage->put($blob, (string) file_get_contents($file), 'image/jpeg');
    echo "Đã tải lên {$blob}\n";
}
echo count($files) . " ảnh.\n";
