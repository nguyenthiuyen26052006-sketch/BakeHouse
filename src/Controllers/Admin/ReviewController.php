<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ReviewRepository;

final class ReviewController extends BaseController
{
    public function __construct(private readonly ReviewRepository $reviews = new ReviewRepository())
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->getQuery('q', ''));
        $visibility = (string) $request->getQuery('visibility', '');
        $paginator = Paginator::fromRequest($request, $this->reviews->adminCount($search, $visibility), 10);

        return $this->render('admin.reviews.index', [
            'title' => 'Đánh giá',
            'rows' => $this->reviews->adminList($search, $visibility, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'search' => $search,
            'visibility' => $visibility,
        ], 'layouts.admin');
    }

    public function toggle(Request $request, string $id): Response
    {
        $review = $this->reviews->find((int) $id);
        if ($review !== null) {
            $this->reviews->setHidden((int) $id, (int) $review['is_hidden'] === 0);
            $this->flash('success', 'Đã cập nhật trạng thái đánh giá.');
        }

        return $this->redirectBack('/admin/reviews');
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->reviews->deleteById((int) $id);
        $this->flash('success', 'Đã xóa đánh giá.');

        return $this->redirectBack('/admin/reviews');
    }
}
