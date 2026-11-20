<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\ErrorHandler;
use App\Core\StreamAborted;
use App\Services\Chatbot\AzureOpenAiClient;
use App\Services\Chatbot\ChatbotTools;
use App\Services\Chatbot\StreamTextFilter;
use Throwable;

final class ChatbotService
{
    private const MAX_ROUNDS = 8;
    private const FALLBACK = 'Xin lỗi, mình đang gặp sự cố. Bạn thử lại sau ít phút nhé.';

    private const SYSTEM_PROMPT = <<<'TXT'
Bạn là trợ lý tư vấn của BakeHouse — cửa hàng bán nguyên vật liệu và dụng cụ làm bánh tại Việt Nam.
Nhiệm vụ: gợi ý nguyên liệu/dụng cụ theo món và ngân sách, so sánh sản phẩm, tra cứu đơn hàng của khách, giải đáp phí ship / thanh toán.

Quy tắc:
- Trả lời tiếng Việt, thân thiện, NGẮN GỌN (dưới 120 từ), không viết công thức chi tiết; có thể dùng markdown nhẹ (danh sách gạch đầu dòng, **in đậm**), không dùng tiêu đề, bảng hay khối code.
- CHỈ giới thiệu sản phẩm lấy từ tool search_products. Không bịa tên, giá hay tồn kho; giá tính bằng VND. Nếu tool không tìm thấy sản phẩm phù hợp thì nói thật là shop chưa có.
- Khi khách nêu một món, tự suy ra danh sách nguyên liệu/dụng cụ cần thiết rồi gọi search_products cho từng thứ — hãy gọi song song nhiều search_products trong cùng một lượt. Chỉ chọn các nguyên liệu/dụng cụ thiết yếu (tối đa khoảng 8 món), mỗi loại 1 sản phẩm phù hợp nhất. Nếu có ngân sách, chọn sao cho tổng (mỗi thứ 1 đơn vị) không vượt ngân sách. KHÔNG tự cộng hay ghi tổng tiền và không liệt kê lại giá từng món: hệ thống tự hiển thị thẻ sản phẩm và tổng tiền ngay dưới lời nhắn của bạn, bạn chỉ nói ngắn gọn vì sao chọn bộ này.
- Câu hỏi về chính sách, theo dõi đơn, đánh giá, thanh toán, ship: dùng get_shop_info (topic faq/shipping/payment/contact).
- Đơn hàng chỉ tra bằng get_my_order_status; chưa đăng nhập thì nhắc khách đăng nhập.
- Câu hỏi không liên quan làm bánh hoặc cửa hàng: từ chối lịch sự và gợi ý quay lại chủ đề.
- Khi muốn hiển thị thẻ sản phẩm cho khách, kết thúc câu trả lời bằng một dòng riêng đúng dạng: IDS: 12,15,20 (id lấy từ kết quả tool). Không có sản phẩm thì bỏ dòng này. Không nhắc đến dòng này trong nội dung.
- Nếu khách nêu ngân sách, thêm một dòng riêng đúng dạng: BUDGET: 300000 (số nguyên VND, tổng ngân sách của cả bộ). Hệ thống sẽ kiểm tra tổng giá các sản phẩm trong IDS so với ngân sách này.
TXT;

    /**
     * @param array<int, array{role: string, content: string}> $history user/assistant turns, last one is the user's question
     * @param (callable(string, array<string, mixed>): void)|null $emit when given, progress is streamed as events:
     *        status {text}, delta {text}, reset {} (discard the text streamed so far)
     * @return array{message: string, products: array<int, array<string, mixed>>, total: int}
     */
    public function chat(array $history, ?callable $emit = null): array
    {
        try {
            $client = new AzureOpenAiClient();
            $tools = new ChatbotTools($client);
            $messages = array_merge([['role' => 'system', 'content' => self::SYSTEM_PROMPT]], $history);
            $budgetRetried = false;
            // Budget stated by the customer is more trustworthy than the one the model echoes back.
            $userBudget = $this->budgetFromUser((string) ($history[array_key_last($history)]['content'] ?? ''));

            for ($round = 0; $round < self::MAX_ROUNDS; $round++) {
                $filter = new StreamTextFilter();
                $streamed = false;
                $onDelta = $emit === null ? null : static function (string $token) use ($filter, $emit, &$streamed): void {
                    $out = $filter->push($token);
                    if ($out !== '') {
                        $streamed = true;
                        $emit('delta', ['text' => $out]);
                    }
                };

                $reply = $client->chat($messages, $tools->definitions(), $onDelta);
                $calls = $reply['tool_calls'] ?? [];

                if ($calls === []) {
                    if ($emit !== null) {
                        $tail = $filter->flush();
                        if ($tail !== '') {
                            $streamed = true;
                            $emit('delta', ['text' => $tail]);
                        }
                    }
                    $content = (string) ($reply['content'] ?? '');
                    $parsed = $this->parse($content, $tools->seenProducts());
                    if ($userBudget > 0) {
                        $parsed['budget'] = $userBudget;
                    }

                    // The model does the arithmetic; the server verifies it and gives the model one chance to fix it.
                    if (!$budgetRetried && $parsed['budget'] > 0 && $parsed['total'] > $parsed['budget']) {
                        $budgetRetried = true;
                        if ($emit !== null) {
                            if ($streamed) {
                                $emit('reset', []);
                            }
                            $emit('status', ['text' => 'Đang điều chỉnh theo ngân sách của bạn…']);
                        }
                        $messages[] = ['role' => 'assistant', 'content' => $content];
                        $messages[] = ['role' => 'user', 'content' => sprintf(
                            '[Hệ thống] Tổng giá các sản phẩm bạn chọn là %s ₫, vượt ngân sách %s ₫. '
                            . 'Hãy chọn lại sản phẩm rẻ hơn (có thể gọi lại search_products với max_price) để tổng không vượt ngân sách. '
                            . 'Sau đó trả lời khách như thể đây là câu trả lời đầu tiên: KHÔNG nhắc đến việc chọn lại hay sai sót, KHÔNG giải thích phép tính, '
                            . 'KHÔNG hỏi ngược lại khách, và vẫn kết thúc bằng dòng IDS: và BUDGET:.',
                            number_format($parsed['total'], 0, ',', '.'),
                            number_format($parsed['budget'], 0, ',', '.')
                        )];
                        continue;
                    }

                    return $this->finish($parsed, $budgetRetried);
                }

                $messages[] = ['role' => 'assistant', 'content' => $reply['content'] ?? null, 'tool_calls' => $calls];
                if ($emit !== null && $streamed) {
                    $emit('reset', []); // text before a tool call is scratch work, not the answer
                }
                if ($emit !== null) {
                    foreach ($calls as $call) {
                        $emit('status', ['text' => $this->statusFor((string) $call['function']['name'], (string) $call['function']['arguments'])]);
                    }
                }
                $tools->warmUp($calls);
                foreach ($calls as $call) {
                    $result = $tools->run((string) $call['function']['name'], (string) $call['function']['arguments']);
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $call['id'],
                        'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ];
                }
            }

            ErrorHandler::logException(new \RuntimeException('Chatbot vượt số vòng gọi tool tối đa.'));

            return ['message' => self::FALLBACK, 'products' => [], 'total' => 0];
        } catch (StreamAborted $e) {
            throw $e; // the visitor left; stop spending tokens
        } catch (Throwable $e) {
            ErrorHandler::logException($e);

            return ['message' => self::FALLBACK, 'products' => [], 'total' => 0];
        }
    }

    /** Human-readable progress line shown while a tool runs. */
    private function statusFor(string $tool, string $argumentsJson): string
    {
        $args = json_decode($argumentsJson, true);
        $query = is_array($args) ? trim((string) ($args['query'] ?? '')) : '';

        return match ($tool) {
            'search_products' => $query !== '' ? 'Đang tìm “' . mb_substr($query, 0, 40) . '”…' : 'Đang tìm sản phẩm…',
            'get_my_order_status' => 'Đang tra cứu đơn hàng của bạn…',
            'get_shop_info' => 'Đang xem thông tin cửa hàng…',
            default => 'Đang xử lý…',
        };
    }

    /** Extracts a VND budget from phrases like "dưới 300k", "ngân sách 1,5 triệu", "tối đa 250.000". 0 when none. */
    private function budgetFromUser(string $text): int
    {
        if (!preg_match('/(?:dưới|ngân sách|tối đa|không quá|budget)\D{0,12}?(\d+(?:[.,]\d+)*)[ \t]*(k|nghìn|ngàn|tr|triệu|m)?\b/iu', $text, $m)) {
            return 0;
        }
        $unit = mb_strtolower($m[2] ?? '');
        $number = $m[1];

        if (in_array($unit, ['tr', 'triệu', 'm'], true)) {
            return (int) round((float) str_replace(',', '.', $number) * 1_000_000);
        }
        $value = (int) preg_replace('/\D+/', '', $number);
        if (in_array($unit, ['k', 'nghìn', 'ngàn'], true) || ($unit === '' && $value > 0 && $value < 1000)) {
            return $value * 1000;
        }

        return $value;
    }

    /**
     * Splits the model output into text, verified products, total and the stated budget.
     * Products are taken from the IDS line; if the model forgot it, fall back to product names mentioned in the text.
     * Either way only products the tools returned this turn can be shown.
     *
     * @param array<int, array<string, mixed>> $seen
     * @return array{content: string, products: array<int, array<string, mixed>>, total: int, budget: int}
     */
    private function parse(string $content, array $seen): array
    {
        $products = [];
        $budget = 0;

        if (preg_match('/\bBUDGET[ \t]*[:=][ \t]*([\d.,]+)/iu', $content, $m)) {
            $budget = (int) preg_replace('/\D+/', '', $m[1]);
        }

        // The model sometimes appends the IDS marker to the end of a sentence instead of its own line.
        if (preg_match('/\bIDS?[ \t]*[:=][ \t]*\[?(\d[\d,\s]*)\]?/i', $content, $m)) {
            foreach (preg_split('/[,\s]+/', trim($m[1])) ?: [] as $id) {
                if (isset($seen[(int) $id])) {
                    $products[(int) $id] = $seen[(int) $id];
                }
            }
        } else {
            foreach ($seen as $id => $p) {
                if (mb_stripos($content, (string) $p['name']) !== false) {
                    $products[(int) $id] = $p;
                }
            }
        }

        $content = preg_replace('/[ \t*_`]*\bIDS?[ \t]*[:=][ \t]*\[?[\d,\s]*\]?[ \t*_`]*/i', '', $content) ?? $content;
        $content = preg_replace('/[ \t*_`]*\bBUDGET[ \t]*[:=][ \t]*[\d.,]+[ \t]*(?:VND|₫|đ)?[ \t*_`]*/iu', '', $content) ?? $content;
        $products = array_values($products);

        return [
            'content' => trim($content),
            'products' => $products,
            'total' => array_sum(array_map(static fn (array $p): int => (int) $p['price'], $products)),
            'budget' => $budget,
        ];
    }

    /**
     * @param array{content: string, products: array<int, array<string, mixed>>, total: int, budget: int} $parsed
     * @return array{message: string, products: array<int, array<string, mixed>>, total: int}
     */
    private function finish(array $parsed, bool $retried = false): array
    {
        $message = $parsed['content'] !== '' ? $parsed['content'] : self::FALLBACK;

        if ($parsed['budget'] > 0 && $parsed['total'] > $parsed['budget']) {
            $notice = sprintf(
                'Tổng ước tính %s ₫ đang vượt ngân sách %s ₫, bạn có thể bỏ bớt món trong danh sách bên dưới.',
                number_format($parsed['total'], 0, ',', '.'),
                number_format($parsed['budget'], 0, ',', '.')
            );
            // After a failed retry the model's draft is unreliable, so show only the server-verified summary.
            $message = $retried || $parsed['products'] === []
                ? 'Mình đã chọn các sản phẩm thiết yếu cho bạn. ' . $notice
                : $message . "\n(Lưu ý: " . $notice . ')';
        }

        return ['message' => $message, 'products' => $parsed['products'], 'total' => $parsed['total']];
    }
}
