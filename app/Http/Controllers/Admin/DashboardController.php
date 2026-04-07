<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function index(): View
    {
        $stats = $this->dashboardService->getStats();

        return view('admin.dashboard', [
            'stats' => $stats,
            'changes' => $this->dashboardService->getWeeklyChanges(),
            'recentApplications' => $this->dashboardService->getRecentApplications(),
            'popularVacancies' => $this->dashboardService->getPopularVacancies(),
            'aiStats' => $this->dashboardService->getAiStats(),
            'applicationsChart' => $this->dashboardService->getApplicationsChartData(),
            'statusChart' => $this->dashboardService->getStatusChartData($stats),
            'recentActivity' => $this->dashboardService->getRecentActivity(),
            'kanbanColumns' => $this->dashboardService->getKanbanColumns(),
            'kanbanApplications' => $this->dashboardService->getKanbanApplications(),
        ]);
    }
}
