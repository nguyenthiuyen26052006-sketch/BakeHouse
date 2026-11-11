<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\StatsRepository;

final class DashboardController extends BaseController
{
    public function index(Request $request): Response
    {
        $stats = new StatsRepository();
        $adminId = (int) Auth::id();

        return $this->render('admin.dashboard', [
            'title' => 'Tổng quan',
            'summary' => $stats->summary($adminId),
            'recentOrders' => $stats->recentOrders($adminId, 6),
            'activities' => $stats->recentActivity($adminId, 8),
            'monthly' => $stats->revenueByMonth($adminId),
            'daily' => $stats->revenueByDay($adminId),
            'topSelling' => $stats->topSelling($adminId, 5),
            'topReviewed' => $stats->topReviewed($adminId, 5),
        ], 'layouts.admin');
    }
}
