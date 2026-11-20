<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\ReportPeriod;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Repositories\OrderRepository;
use App\Repositories\StatsRepository;

/** Printable sales report for the period chosen on the dashboard. */
final class ReportController extends BaseController
{
    public function print(Request $request): Response
    {
        $period = ReportPeriod::fromRequest($request);
        $stats = new StatsRepository();

        $html = View::render('admin.reports.print', [
            'title' => 'Báo cáo doanh số',
            'period' => $period,
            'rows' => (new OrderRepository())->listBetween($period->from, $period->to),
            'summary' => $stats->summary($period),
            'topSelling' => $stats->topSelling($period, 10),
        ], null);

        return Response::html($html);
    }
}
