<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\ReportPeriod;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\StatsRepository;

final class DashboardController extends BaseController
{
    public function index(Request $request): Response
    {
        $stats = new StatsRepository();
        $period = ReportPeriod::fromRequest($request);

        return $this->render('admin.dashboard', [
            'title' => 'Tổng quan',
            'period' => $period,
            'summary' => $stats->summary($period),
            'chart' => $stats->series($period),
            'topSelling' => $stats->topSelling($period, 6),
            'attention' => $stats->attention(),
            'recentOrders' => $stats->recentOrders(6),
        ], 'layouts.admin');
    }
}
