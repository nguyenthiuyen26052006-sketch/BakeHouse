<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\ChatbotService;

final class ChatbotApiController extends BaseController
{
    public function suggest(Request $request): Response
    {
        $dish = trim((string) $request->getInput('dish', ''));
        $budget = max(0, (int) preg_replace('/\D+/', '', (string) $request->getInput('budget', '0')));

        if ($dish === '' || mb_strlen($dish) > 120) {
            return $this->json(['ok' => false, 'message' => 'Hãy cho mình biết bạn muốn làm món gì nhé.'], 422);
        }

        $result = (new ChatbotService())->suggest($dish, $budget);

        return $this->json([
            'ok' => true,
            'message' => $result['message'],
            'total' => format_vnd($result['total']),
            'products' => array_map(static fn (array $p): array => [
                'id' => (int) $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'unit' => $p['unit'],
                'price' => format_vnd((int) $p['price']),
                'image' => img_url($p['image_path']),
            ], $result['products']),
        ]);
    }
}
