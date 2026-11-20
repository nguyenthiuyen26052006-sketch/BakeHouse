<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Core\Env;
use App\Core\ErrorHandler;
use App\Core\RateLimiter;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\StreamAborted;
use App\Services\ChatbotService;
use Throwable;

final class ChatbotApiController extends BaseController
{
    private const MAX_TURNS = 10;
    private const MAX_LENGTH = 500;
    private const RATE_LIMIT = 20;
    private const RATE_WINDOW = 600;
    private const IP_LIMIT = 40;
    private const GLOBAL_LIMIT = 600;
    private const GLOBAL_WINDOW = 3600;

    /** Plain JSON endpoint (also the fallback for browsers that cannot read a stream). */
    public function chat(Request $request): Response
    {
        $error = $this->precheck($request, $history);
        if ($error !== null) {
            return $error;
        }

        $result = (new ChatbotService())->chat($history);

        return $this->json(['ok' => true] + $this->payload($result));
    }

    /**
     * Server-Sent Events endpoint. Events: status, delta, reset (progress) then final {message, total, products, sig}
     * and done; error {message} on failure.
     */
    public function stream(Request $request): Response
    {
        $error = $this->precheck($request, $history);
        if ($error !== null) {
            return $error;
        }

        $this->secret();
        // The stream can run for many seconds; release the session lock so cart/page requests are not blocked.
        // Session data stays readable (e.g. the logged-in user for order lookups).
        session_write_close();

        return Response::stream(function (callable $emit) use ($history): void {
            try {
                $result = (new ChatbotService())->chat($history, $emit);
                $emit('final', $this->payload($result));
            } catch (StreamAborted $e) {
                throw $e;
            } catch (Throwable $e) {
                ErrorHandler::logException($e);
                $emit('error', ['message' => 'Xin lỗi, mình đang gặp sự cố. Bạn thử lại sau ít phút nhé.']);
            }
            $emit('done', []);
        });
    }

    /**
     * Shared validation + throttling. Returns an error response, or null with $history filled in.
     *
     * @param array<int, array{role: string, content: string}>|null $history
     */
    private function precheck(Request $request, ?array &$history): ?Response
    {
        $history = $this->history($request->getInput('messages', '[]'));
        if ($history === [] || end($history)['role'] !== 'user') {
            return $this->json(['ok' => false, 'message' => 'Hãy nhập câu hỏi của bạn nhé.'], 422);
        }
        if (!$this->allow($request)) {
            return $this->json(['ok' => false, 'message' => 'Bạn hỏi hơi nhanh, vui lòng thử lại sau vài phút nhé.'], 429);
        }

        return null;
    }

    /**
     * @param array{message: string, products: array<int, array<string, mixed>>, total: int} $result
     * @return array<string, mixed>
     */
    private function payload(array $result): array
    {
        return [
            'message' => $result['message'],
            'sig' => $this->sign($result['message']),
            'total' => format_vnd($result['total']),
            'total_raw' => $result['total'],
            'products' => array_map(static fn (array $p): array => [
                'id' => (int) $p['id'],
                'name' => $p['name'],
                'slug' => $p['slug'],
                'unit' => $p['unit'],
                'price' => format_vnd((int) $p['price']),
                'image' => img_url($p['image_path']),
            ], $result['products']),
        ];
    }

    /** Per-session secret used to sign assistant replies, created before streaming starts. */
    private function secret(): string
    {
        $secret = Session::get('chatbot_secret');
        if (!is_string($secret) || $secret === '') {
            $secret = bin2hex(random_bytes(32));
            Session::set('chatbot_secret', $secret);
        }

        return $secret;
    }

    private function sign(string $content): string
    {
        return hash_hmac('sha256', trim($content), $this->secret());
    }

    /** @return array<int, array{role: string, content: string}> */
    private function history(mixed $raw): array
    {
        $items = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($items)) {
            return [];
        }

        // The client could forge "assistant" turns to steer the model, so only replies this server
        // signed for this session are accepted back as assistant history.
        $secret = Session::get('chatbot_secret');

        $clean = [];
        foreach (array_slice($items, -self::MAX_TURNS) as $item) {
            $role = is_array($item) ? ($item['role'] ?? '') : '';
            $content = is_array($item) ? trim((string) ($item['content'] ?? '')) : '';
            if (($role !== 'user' && $role !== 'assistant') || $content === '') {
                continue;
            }
            if ($role === 'assistant' && !$this->validSignature($content, $secret, $item['sig'] ?? null)) {
                continue;
            }
            $clean[] = ['role' => $role, 'content' => mb_substr($content, 0, self::MAX_LENGTH)];
        }

        return $clean;
    }

    private function validSignature(string $content, mixed $secret, mixed $sig): bool
    {
        return is_string($secret) && $secret !== '' && is_string($sig)
            && hash_equals(hash_hmac('sha256', $content, $secret), $sig);
    }

    /** Protects the paid API: per session, per IP (cookie-proof) and overall budget. */
    private function allow(Request $request): bool
    {
        $now = time();
        $hits = array_values(array_filter(
            (array) Session::get('chatbot_hits', []),
            static fn (int $t): bool => $t > $now - self::RATE_WINDOW
        ));
        if (count($hits) >= self::RATE_LIMIT) {
            Session::set('chatbot_hits', $hits);

            return false;
        }
        $hits[] = $now;
        Session::set('chatbot_hits', $hits);

        return RateLimiter::hit('chatbot:ip:' . $request->getIp(), self::IP_LIMIT, self::RATE_WINDOW)
            && RateLimiter::hit('chatbot:global', Env::int('CHATBOT_GLOBAL_LIMIT', self::GLOBAL_LIMIT), self::GLOBAL_WINDOW);
    }
}
