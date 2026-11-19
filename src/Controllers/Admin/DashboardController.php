<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\StatsRepository;

final class DashboardController extends BaseController
{
    public function index(Request $request): Response
    {
        $stats = new StatsRepository();

        return $this->render('admin.dashboard', [
            'title' => 'Tổng quan',
            'summary' => $stats->summary(),
            'recentOrders' => $stats->recentOrders(6),
            'activities' => $stats->recentActivity(8),
            'monthly' => $stats->revenueByMonth(),
            'daily' => $stats->revenueByDay(),
            'topSelling' => $stats->topSelling(5),
            'topReviewed' => $stats->topReviewed(5),
        ], 'layouts.admin');
    }
}
