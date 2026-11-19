<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Paginator;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\OrderRepository;

final class ReportController extends BaseController
{
    private const PER_PAGE = 20;

    public function __construct(private readonly OrderRepository $orders = new OrderRepository())
    {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->getQuery('q', ''));
        $month = trim((string) $request->getQuery('month', ''));
        $paginator = Paginator::fromRequest($request, $this->orders->reportCount($search, $month), self::PER_PAGE);

        return $this->render('admin.reports.index', [
            'title' => 'In báo cáo',
            'rows' => $this->orders->reportList($search, $month, $paginator->getLimit(), $paginator->getOffset()),
            'summary' => $this->orders->reportSummary($search, $month),
            'paginator' => $paginator,
            'search' => $search,
            'month' => $month,
        ], 'layouts.admin');
    }

    public function print(Request $request): Response
    {
        $search = trim((string) $request->getQuery('q', ''));
        $month = trim((string) $request->getQuery('month', ''));

        $html = View::render('admin.reports.print', [
            'title' => 'Báo cáo đơn hàng',
            'rows' => $this->orders->reportAll($search, $month),
            'summary' => $this->orders->reportSummary($search, $month),
            'search' => $search,
            'month' => $month,
        ], null);

        return Response::html($html);
    }
}
