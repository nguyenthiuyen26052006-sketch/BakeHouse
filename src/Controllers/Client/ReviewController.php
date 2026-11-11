<?php

declare(strict_types=1);

namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Repositories\ProductRepository;
use App\Services\ReviewService;
use DomainException;

final class ReviewController extends BaseController
{
    public function save(Request $request, string $slug): Response
    {
        $product = (new ProductRepository())->findBySlug($slug);
        if ($product === null) {
            return ErrorHandler::render(404, $request);
        }

        $validator = new Validator($request->getAllInput(), ['rating' => 'required|integer|in:1,2,3,4,5', 'comment' => 'max:1000']);
        if (!$validator->validate()) {
            return $this->redirectWithErrors($request, '/products/' . $slug . '#reviews', $validator->errors());
        }

        try {
            (new ReviewService())->submit(
                (int) Auth::id(),
                (int) $product['id'],
                (int) $request->getInput('rating'),
                trim((string) $request->getInput('comment')) ?: null
            );
            $this->flash('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
        } catch (DomainException $e) {
            $this->flash('danger', $e->getMessage());
        }

        return $this->redirect('/products/' . $slug . '#reviews');
    }

    public function delete(Request $request, string $slug): Response
    {
        $product = (new ProductRepository())->findBySlug($slug);
        if ($product === null) {
            return ErrorHandler::render(404, $request);
        }
        (new ReviewService())->remove((int) Auth::id(), (int) $product['id']);
        $this->flash('success', 'Đã xóa đánh giá của bạn.');

        return $this->redirect('/products/' . $slug . '#reviews');
    }
}
