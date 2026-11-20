<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Core\Env;
use RuntimeException;

/** Azure Blob Storage over the REST API (SharedKey auth, cURL). No SDK needed. */
final class AzureBlobStorage
{
    private const VERSION = '2021-08-06';

    private string $account = '';
    private string $key = '';
    private string $suffix = 'core.windows.net';
    private string $protocol = 'https';
    private string $container = '';

    public function __construct()
    {
        $parts = [];
        foreach (explode(';', (string) Env::get('AZURE_STORAGE_CONNECTION_STRING', '')) as $pair) {
            if (str_contains($pair, '=')) {
                [$k, $v] = explode('=', $pair, 2);
                $parts[trim($k)] = trim($v);
            }
        }
        $this->account = $parts['AccountName'] ?? '';
        $this->key = $parts['AccountKey'] ?? '';
        $this->suffix = $parts['EndpointSuffix'] ?? $this->suffix;
        $this->protocol = $parts['DefaultEndpointsProtocol'] ?? $this->protocol;
        $this->container = (string) Env::get('AZURE_STORAGE_CONTAINER_NAME', '');
    }

    public static function isConfigured(): bool
    {
        $s = new self();

        return $s->account !== '' && $s->key !== '' && $s->container !== '';
    }

    /** Plain blob URL (works anonymously only if the container allows public blob read). */
    public function url(string $blobPath): string
    {
        return sprintf('%s://%s.blob.%s/%s/%s', $this->protocol, $this->account, $this->suffix, $this->container, $this->encode($blobPath));
    }

    /**
     * Browser-readable URL: blob URL + read-only container SAS, so images work even when the container is private.
     * The expiry is fixed to the 1st of a month ~13 months ahead, keeping URLs stable (cache-friendly) within a month.
     */
    public function readUrl(string $blobPath): string
    {
        return $this->url($blobPath) . '?' . $this->containerSas();
    }

    private function containerSas(): string
    {
        $expiry = gmdate('Y-m-01\T00:00:00\Z', strtotime('first day of +13 months'));
        $fields = ['r', '', $expiry, '/blob/' . $this->account . '/' . $this->container, '', '', 'https', self::VERSION, 'c', '', '', '', '', '', '', ''];
        $signature = base64_encode(hash_hmac('sha256', implode("\n", $fields), (string) base64_decode($this->key, true), true));

        return http_build_query(['sv' => self::VERSION, 'spr' => 'https', 'se' => $expiry, 'sr' => 'c', 'sp' => 'r', 'sig' => $signature], '', '&', PHP_QUERY_RFC3986);
    }

    public function put(string $blobPath, string $content, string $contentType): void
    {
        $this->send('PUT', $blobPath, $content, $contentType, ['x-ms-blob-type' => 'BlockBlob'], [201]);
    }

    public function delete(string $blobPath): void
    {
        $this->send('DELETE', $blobPath, '', '', [], [202, 404]);
    }

    /** @param array<string, string> $extra x-ms-* headers @param array<int, int> $okStatuses */
    private function send(string $method, string $blobPath, string $body, string $contentType, array $extra, array $okStatuses): void
    {
        $headers = $extra + ['x-ms-date' => gmdate('D, d M Y H:i:s') . ' GMT', 'x-ms-version' => self::VERSION];
        ksort($headers);
        $canonHeaders = '';
        foreach ($headers as $k => $v) {
            $canonHeaders .= strtolower($k) . ':' . $v . "\n";
        }
        $length = $body === '' ? '' : (string) strlen($body);
        $resource = '/' . $this->account . '/' . $this->container . '/' . $this->encode($blobPath);
        $stringToSign = implode("\n", [$method, '', '', $length, '', $contentType, '', '', '', '', '', '']) . "\n" . $canonHeaders . $resource;
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, (string) base64_decode($this->key, true), true));

        $curlHeaders = ['Authorization: SharedKey ' . $this->account . ':' . $signature];
        foreach ($headers as $k => $v) {
            $curlHeaders[] = "{$k}: {$v}";
        }
        if ($contentType !== '') {
            $curlHeaders[] = 'Content-Type: ' . $contentType;
        }

        $ch = curl_init($this->url($blobPath));
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => $curlHeaders,
        ]);
        if ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Không kết nối được Azure Blob Storage: ' . $error);
        }
        if (!in_array($status, $okStatuses, true)) {
            throw new RuntimeException(sprintf('Azure Blob Storage lỗi HTTP %d: %s', $status, substr((string) $response, 0, 300)));
        }
    }

    private function encode(string $blobPath): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $blobPath)));
    }
}
