<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\Storage\AzureBlobStorage;
use RuntimeException;

final class FileUploader
{
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    private const MAX_SIZE = 2 * 1024 * 1024;

    public function __construct(private readonly string $uploadRoot = '')
    {
    }

    /**
     * Validate and store an uploaded image. Returns the path relative to public/ (e.g. "uploads/products/abc.jpg").
     *
     * @param array{name: string, tmp_name: string, error: int, size: int} $file entry from $_FILES
     * @throws RuntimeException with a user-facing Vietnamese message
     */
    public function upload(array $file, string $subDirectory): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Tải tệp lên thất bại, vui lòng thử lại.');
        }
        if (($file['size'] ?? 0) > self::MAX_SIZE) {
            throw new RuntimeException('Ảnh không được vượt quá 2MB.');
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Tệp tải lên không hợp lệ.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED_MIME[$mime]) || @getimagesize($file['tmp_name']) === false) {
            throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
        }

        $subDirectory = trim(preg_replace('/[^a-z0-9_\/-]/i', '', $subDirectory), '/');
        if (AzureBlobStorage::isConfigured()) {
            $blobPath = 'uploads/' . $subDirectory . '/' . bin2hex(random_bytes(16)) . '.' . self::ALLOWED_MIME[$mime];
            try {
                (new AzureBlobStorage())->put($blobPath, (string) file_get_contents($file['tmp_name']), $mime);
            } catch (\Throwable $e) {
                ErrorHandler::logException($e);
                throw new RuntimeException('Không thể lưu ảnh lên kho lưu trữ.');
            }

            return $blobPath;
        }

        $root = $this->uploadRoot !== '' ? $this->uploadRoot : BASE_PATH . '/public/uploads';
        $directory = $root . '/' . $subDirectory;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu ảnh.');
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_MIME[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $fileName)) {
            throw new RuntimeException('Không thể lưu ảnh.');
        }

        return 'uploads/' . $subDirectory . '/' . $fileName;
    }

    /** Delete a previously uploaded file given its public-relative path. Ignores paths outside uploads/. */
    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || !str_starts_with($relativePath, 'uploads/') || str_contains($relativePath, '..')) {
            return;
        }

        if (AzureBlobStorage::isConfigured()) {
            try {
                (new AzureBlobStorage())->delete($relativePath);
            } catch (\Throwable $e) {
                ErrorHandler::logException($e);
            }

            return;
        }

        $fullPath = BASE_PATH . '/public/' . $relativePath;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
