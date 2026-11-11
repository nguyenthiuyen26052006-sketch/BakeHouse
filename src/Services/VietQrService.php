<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;

final class VietQrService
{
    /** QR image (img.vietqr.io) for the exact amount, with the order code as transfer content. */
    public function buildImageUrl(string $orderCode, int $amount): string
    {
        return sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
            rawurlencode((string) Env::get('VIETQR_BANK_ID', '970436')),
            rawurlencode((string) Env::get('VIETQR_ACCOUNT_NO', '0000000000')),
            $amount,
            rawurlencode($orderCode),
            rawurlencode((string) Env::get('VIETQR_ACCOUNT_NAME', 'BAKEHOUSE'))
        );
    }

    /** @return array{bank_id: string, account_no: string, account_name: string} */
    public function accountInfo(): array
    {
        return [
            'bank_id' => (string) Env::get('VIETQR_BANK_ID', ''),
            'account_no' => (string) Env::get('VIETQR_ACCOUNT_NO', ''),
            'account_name' => (string) Env::get('VIETQR_ACCOUNT_NAME', ''),
        ];
    }
}
