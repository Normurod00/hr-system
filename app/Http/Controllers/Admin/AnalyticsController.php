<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    public function candidates(Request $request): View
    {
        $period = $request->get('period', '30');
        $since = now()->subDays((int) $period);

        return view('admin.analytics.candidates', [
            'kpi' => $this->analyticsService->getCandidateKpi($since),
            'statusDistribution' => $this->analyticsService->getStatusDistribution(),
            'scoreDistribution' => $this->analyticsService->getScoreDistribution(),
            'recommendationDistribution' => $this->analyticsService->getRecommendationDistribution(),
            'aiOpsStats' => $this->analyticsService->getAiOpsStats(),
            'applicationsTrend' => $this->analyticsService->getApplicationsTrend(min((int) $period, 30)),
            'topVacancies' => $this->analyticsService->getTopVacancies(),
            'funnel' => $this->analyticsService->getConversionFunnel(),
            'period' => $period,
        ]);
    }

    public function employees(Request $request): View
    {
        return view('admin.analytics.employees', [
            'kpi' => $this->analyticsService->getEmployeeKpi(),
            'departmentDistribution' => $this->analyticsService->getDepartmentDistribution(),
            'contextTypes' => $this->analyticsService->getContextTypes(),
            'recommendationTypes' => $this->analyticsService->getRecommendationTypes(),
            'aiUsageTrend' => $this->analyticsService->getAiUsageTrend(),
            'docTypeDistribution' => $this->analyticsService->getDocTypeDistribution(),
        ]);
    }
}
