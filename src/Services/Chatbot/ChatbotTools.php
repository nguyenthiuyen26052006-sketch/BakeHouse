<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Core\Auth;
use App\Core\Env;
use App\Enums\OrderStatus;
use App\Repositories\OrderRepository;
use App\Repositories\ProductEmbeddingRepository;
use App\Repositories\ProductRepository;

/** Tools the model may call. All facts (price, stock, orders) come from the database. */
final class ChatbotTools
{
    private const LAZY_SYNC_LIMIT = 50;
    private const DEFAULT_MIN_SCORE = 0.30;

    /** @var array<int, array<string, mixed>> products returned by search_products this turn, keyed by id */
    private array $seen = [];

    /** @var array<int, array{product_id: int, price: int, embedding: array<int, float>, norm: float}>|null loaded once per request */
    private ?array $index = null;

    /** @var array<string, array<int, float>> query text => embedding, filled in one batch by warmUp() */
    private array $queryVectors = [];

    public function __construct(
        private readonly AzureOpenAiClient $client,
        private readonly ProductRepository $products = new ProductRepository(),
        private readonly ProductEmbeddingRepository $embeddings = new ProductEmbeddingRepository(),
        private readonly OrderRepository $orders = new OrderRepository(),
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function seenProducts(): array
    {
        return $this->seen;
    }

    /**
     * Embeds every search query of one model turn in a single API call (the model asks for ~8 in parallel),
     * instead of one round-trip per query.
     *
     * @param array<int, array<string, mixed>> $calls tool_calls as returned by the model
     */
    public function warmUp(array $calls): void
    {
        $queries = [];
        foreach ($calls as $call) {
            if (($call['function']['name'] ?? '') !== 'search_products') {
                continue;
            }
            $args = json_decode((string) ($call['function']['arguments'] ?? ''), true);
            $query = is_array($args) ? trim((string) ($args['query'] ?? '')) : '';
            if ($query !== '' && !isset($this->queryVectors[$query])) {
                $queries[$query] = true;
            }
        }
        if ($queries === []) {
            return;
        }

        $texts = array_keys($queries);
        foreach ($this->client->embed($texts) as $i => $vector) {
            $this->queryVectors[$texts[$i]] = $vector;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_products',
                    'description' => 'Tìm nguyên vật liệu / dụng cụ làm bánh còn hàng trong shop theo ngữ nghĩa. '
                        . 'Gọi nhiều lần với các truy vấn khác nhau (vd mỗi nguyên liệu một lần). Giá luôn tính bằng VND.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Mô tả thứ cần tìm, vd "bột mì đa dụng", "khuôn bánh tròn".'],
                            'max_price' => ['type' => 'integer', 'description' => 'Giá tối đa mỗi sản phẩm (VND), bỏ qua nếu không giới hạn.'],
                            'limit' => ['type' => 'integer', 'description' => 'Số kết quả, 1-8, mặc định 4.'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_my_order_status',
                    'description' => 'Tra cứu các đơn hàng gần nhất của người dùng đang đăng nhập. Không cần tham số.',
                    'parameters' => ['type' => 'object', 'properties' => new \stdClass()],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_shop_info',
                    'description' => 'Thông tin cửa hàng: phí vận chuyển, hình thức thanh toán, liên hệ, hoặc faq (các câu hỏi thường gặp của shop: theo dõi đơn, đánh giá, quên mật khẩu...).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'topic' => ['type' => 'string', 'enum' => ['shipping', 'payment', 'contact', 'faq']],
                        ],
                        'required' => ['topic'],
                    ],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function run(string $name, string $argumentsJson): array
    {
        $args = json_decode($argumentsJson, true);
        $args = is_array($args) ? $args : [];

        return match ($name) {
            'search_products' => $this->searchProducts(
                trim((string) ($args['query'] ?? '')),
                max(0, (int) ($args['max_price'] ?? 0)),
                min(8, max(1, (int) ($args['limit'] ?? 4)))
            ),
            'get_my_order_status' => $this->myOrders(),
            'get_shop_info' => $this->shopInfo((string) ($args['topic'] ?? '')),
            default => ['error' => 'Tool không tồn tại.'],
        };
    }

    /** @return array<string, mixed> */
    private function searchProducts(string $query, int $maxPrice, int $limit): array
    {
        if ($query === '') {
            return ['error' => 'Thiếu query.'];
        }

        $queryVector = $this->queryVectors[$query] ??= $this->client->embed([$query])[0];
        $queryNorm = $this->norm($queryVector);
        $minScore = (float) (Env::get('CHATBOT_MIN_SCORE') ?? self::DEFAULT_MIN_SCORE);

        $scores = [];
        foreach ($this->index() as $row) {
            if ($maxPrice > 0 && $row['price'] > $maxPrice) {
                continue;
            }
            $score = $this->cosine($queryVector, $queryNorm, $row['embedding'], $row['norm']);
            if ($score >= $minScore) {
                $scores[$row['product_id']] = $score;
            }
        }
        arsort($scores);
        $ids = array_slice(array_keys($scores), 0, $limit);

        $byId = [];
        foreach ($this->products->findActiveByIds($ids) as $p) {
            $byId[(int) $p['id']] = $p;
        }

        $results = [];
        foreach ($ids as $id) {
            if (!isset($byId[$id])) {
                continue;
            }
            $p = $byId[$id];
            $this->seen[$id] = $p;
            $results[] = [
                'id' => $id,
                'name' => $p['name'],
                'price_vnd' => (int) $p['price'],
                'unit' => $p['unit'],
                'rating' => (float) $p['rating_avg'],
                'in_stock' => (int) $p['stock_quantity'],
            ];
        }

        if ($results === []) {
            return ['products' => [], 'note' => 'Shop không có sản phẩm phù hợp với truy vấn này. Hãy nói thật với khách, không bịa sản phẩm.'];
        }

        return ['products' => $results];
    }

    /**
     * Product vectors, decoded and normalised once per request (search_products is often called many times per turn).
     *
     * @return array<int, array{product_id: int, price: int, embedding: array<int, float>, norm: float}>
     */
    private function index(): array
    {
        if ($this->index !== null) {
            return $this->index;
        }

        // Cover products added since the last index build, bounded so one request cannot run for long.
        if ($this->embeddings->countMissing() > 0) {
            (new ProductEmbeddingIndexer($this->client, $this->embeddings))->sync(null, self::LAZY_SYNC_LIMIT);
        }

        $this->index = array_map(function (array $row): array {
            $row['norm'] = $this->norm($row['embedding']);

            return $row;
        }, $this->embeddings->searchable());

        return $this->index;
    }

    /** @return array<string, mixed> */
    private function myOrders(): array
    {
        $userId = Auth::id();
        if ($userId === null) {
            return ['error' => 'Người dùng chưa đăng nhập. Hãy nhờ họ đăng nhập để tra cứu đơn hàng.'];
        }
        if (!Auth::isCustomer()) {
            return ['error' => 'Tài khoản quản trị không có đơn hàng mua sắm.'];
        }

        $orders = [];
        foreach ($this->orders->listByUser($userId, 3, 0) as $o) {
            $orders[] = [
                'order_code' => $o['order_code'],
                'status' => OrderStatus::from((string) $o['status'])->label(),
                'payment_method' => $o['payment_method'] === 'vietqr' ? 'VietQR' : 'COD',
                'payment_status' => $o['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán',
                'total_vnd' => (int) $o['total_amount'],
                'item_count' => (int) $o['item_count'],
                'created_at' => $o['created_at'],
            ];
        }

        return $orders === [] ? ['message' => 'Người dùng chưa có đơn hàng nào.'] : ['orders' => $orders];
    }

    /** @return array<string, mixed> */
    private function shopInfo(string $topic): array
    {
        return match ($topic) {
            'shipping' => ['shipping_fee_vnd' => max(0, Env::int('SHIPPING_FEE', 30000)), 'note' => 'Phí giao hàng cố định cho mỗi đơn.'],
            'payment' => ['methods' => ['COD: trả tiền mặt khi nhận hàng', 'VietQR: quét mã QR chuyển khoản, admin xác nhận sau khi nhận tiền']],
            'faq' => ['faqs' => array_map(
                static fn (array $f): array => ['question' => $f[0], 'answer' => $f[1]],
                require BASE_PATH . '/src/Data/faq.php'
            )],
            'contact' => ['note' => 'Khách gửi yêu cầu qua form Liên hệ trên website, shop sẽ phản hồi.'],
            default => ['error' => 'Chủ đề không hỗ trợ.'],
        };
    }

    /** @param array<int, float> $v */
    private function norm(array $v): float
    {
        $sum = 0.0;
        foreach ($v as $x) {
            $sum += $x * $x;
        }

        return sqrt($sum);
    }

    /** @param array<int, float> $a @param array<int, float> $b */
    private function cosine(array $a, float $na, array $b, float $nb): float
    {
        if ($na <= 0.0 || $nb <= 0.0) {
            return 0.0;
        }
        $dot = 0.0;
        foreach ($a as $i => $x) {
            $dot += $x * ($b[$i] ?? 0.0);
        }

        return $dot / ($na * $nb);
    }
}
