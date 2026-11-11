<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReviewRepository;
use DomainException;

final class ReviewService
{
    public function __construct(private readonly ReviewRepository $reviews = new ReviewRepository())
    {
    }

    public function canReview(int $userId, int $productId): bool
    {
        return $this->reviews->hasPurchasedDelivered($userId, $productId);
    }

    /**
     * Create or update the user's single review of a product.
     *
     * @throws DomainException when the user has not received the product
     */
    public function submit(int $userId, int $productId, int $rating, ?string $comment): void
    {
        if (!$this->canReview($userId, $productId)) {
            throw new DomainException('Chỉ khách hàng đã nhận hàng mới có thể đánh giá sản phẩm này.');
        }
        if ($rating < 1 || $rating > 5) {
            throw new DomainException('Số sao phải từ 1 đến 5.');
        }
        $this->reviews->save($userId, $productId, $rating, $comment);
    }

    public function remove(int $userId, int $productId): void
    {
        $this->reviews->deleteByUserAndProduct($userId, $productId);
    }
}
