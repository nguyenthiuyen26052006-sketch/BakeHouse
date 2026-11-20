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
        $view = (string) $request->getQuery('reply', '');
        if ($view !== 'none') {
            $view = '';
        }
        $view = $view === 'none' ? 'unanswered' : $view;
        $rating = (int) $request->getQuery('rating', 0);
        $paginator = Paginator::fromRequest($request, $this->reviews->adminCount($search, $view, $rating), 10);

        return $this->render('admin.reviews.index', [
            'title' => 'Đánh giá',
            'rows' => $this->reviews->adminList($search, $view, $rating, $paginator->getLimit(), $paginator->getOffset()),
            'paginator' => $paginator,
            'counts' => $this->reviews->viewCounts(),
            'search' => $search,
            'view' => $view === 'unanswered' ? 'none' : $view,
            'rating' => $rating,
        ], 'layouts.admin');
    }

    public function reply(Request $request, string $id): Response
    {
        $review = $this->reviews->find((int) $id);
        if ($review === null) {
            return $this->redirectBack('/admin/reviews');
        }

        $reply = trim((string) $request->getInput('admin_reply'));
        $this->reviews->reply((int) $id, $reply !== '' ? $reply : null);
        $this->flash('success', $reply !== '' ? 'Đã lưu phản hồi.' : 'Đã xóa phản hồi.');

        return $this->redirectBack('/admin/reviews');
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->reviews->deleteById((int) $id);
        $this->flash('success', 'Đã xóa đánh giá.');

        return $this->redirectBack('/admin/reviews');
    }
}
