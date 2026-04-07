<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationAnalysis;
use App\Models\ApplicationFile;
use App\Models\EmployeeAiConversation;
use App\Models\EmployeeAiMessage;
use App\Models\EmployeeAiRecommendation;
use App\Models\EmployeeDocument;
use App\Models\EmployeeProfile;
use App\Models\Vacancy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getCandidateKpi(string $since): array
    {
        $totalApplications = Application::count();
        $periodApplications = Application::where('created_at', '>=', $since)->count();
        $analyzedApplications = Application::whereNotNull('match_score')->count();
        $withAnalysis = ApplicationAnalysis::count();
        $avgMatchScore = Application::whereNotNull('match_score')->avg('match_score');
        $totalDocuments = ApplicationFile::count();
        $parsedDocuments = ApplicationFile::where('is_parsed', true)->count();
        $failedDocuments = ApplicationFile::where('is_parsed', false)
            ->whereRaw("created_at < NOW() - INTERVAL '1 hour'")
            ->count();

        return [
            'total_applications' => $totalApplications,
            'period_applications' => $periodApplications,
            'analyzed_applications' => $analyzedApplications,
            'with_analysis' => $withAnalysis,
            'avg_match_score' => round($avgMatchScore ?? 0, 1),
            'total_documents' => $totalDocuments,
            'parsed_documents' => $parsedDocuments,
            'failed_documents' => $failedDocuments,
            'analysis_coverage' => $totalApplications > 0
                ? round(($analyzedApplications / $totalApplications) * 100, 1)
                : 0,
            'parse_success_rate' => $totalDocuments > 0
                ? round(($parsedDocuments / $totalDocuments) * 100, 1)
                : 0,
        ];
    }

    public function getStatusDistribution(): array
    {
        $counts = Application::query()
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $distribution = [];
        foreach (ApplicationStatus::cases() as $status) {
            $distribution[] = [
                'status' => $status->label(),
                'count' => $counts[$status->value] ?? 0,
                'color' => match ($status) {
                    ApplicationStatus::New => '#3b82f6',
                    ApplicationStatus::InReview => '#f59e0b',
                    ApplicationStatus::Invited => '#8b5cf6',
                    ApplicationStatus::Hired => '#22c55e',
                    ApplicationStatus::Rejected => '#ef4444',
                },
            ];
        }

        return $distribution;
    }

    public function getScoreDistribution(): Collection
    {
        return DB::table('applications')
            ->whereNotNull('match_score')
            ->selectRaw("
                CASE
                    WHEN match_score >= 90 THEN '90-100'
                    WHEN match_score >= 80 THEN '80-89'
                    WHEN match_score >= 70 THEN '70-79'
                    WHEN match_score >= 60 THEN '60-69'
                    WHEN match_score >= 50 THEN '50-59'
                    WHEN match_score >= 40 THEN '40-49'
                    WHEN match_score >= 30 THEN '30-39'
                    ELSE '0-29'
                END as bucket,
                COUNT(*) as count
            ")
            ->groupBy('bucket')
            ->orderByRaw("MIN(match_score) DESC")
            ->get();
    }

    public function getRecommendationDistribution(): Collection
    {
        return DB::table('application_analyses')
            ->select('recommendation', DB::raw('COUNT(*) as count'))
            ->groupBy('recommendation')
            ->orderByDesc('count')
            ->get();
    }

    public function getAiOpsStats(int $days = 7): Collection
    {
        return DB::table('ai_logs')
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                'operation',
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success"),
                DB::raw("SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors"),
                DB::raw("AVG(duration_ms) as avg_duration")
            )
            ->groupBy('operation')
            ->get();
    }

    /**
     * Optimized: single grouped query instead of per-day loop
     */
    public function getApplicationsTrend(int $days): array
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $dailyCounts = Application::where('created_at', '>=', $since)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count, SUM(CASE WHEN match_score IS NOT NULL THEN 1 ELSE 0 END) as analyzed")
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->toDateString();
            $row = $dailyCounts->get($dateStr);
            $trend[] = [
                'date' => $date->format('d.m'),
                'count' => $row?->count ?? 0,
                'analyzed' => $row?->analyzed ?? 0,
            ];
        }

        return $trend;
    }

    /**
     * Optimized: use subquery for avg_score instead of N+1
     */
    public function getTopVacancies(int $limit = 10): Collection
    {
        return Vacancy::has('applications')
            ->withCount([
                'applications',
                'applications as analyzed_count' => fn($q) => $q->whereNotNull('match_score'),
            ])
            ->withAvg([
                'applications as avg_score' => fn($q) => $q->whereNotNull('match_score'),
            ], 'match_score')
            ->orderByDesc('applications_count')
            ->limit($limit)
            ->get()
            ->map(fn($v) => [
                'title' => $v->title,
                'applications' => $v->applications_count,
                'analyzed' => $v->analyzed_count,
                'avg_score' => round($v->avg_score ?? 0, 1),
            ]);
    }

    public function getConversionFunnel(): array
    {
        $counts = Application::query()
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN match_score IS NOT NULL THEN 1 ELSE 0 END) as analyzed")
            ->first();

        $statusCounts = Application::query()
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            ['stage' => 'Всего заявок', 'count' => $counts->total ?? 0],
            ['stage' => 'AI проанализированы', 'count' => $counts->analyzed ?? 0],
            ['stage' => 'На рассмотрении', 'count' => $statusCounts[ApplicationStatus::InReview->value] ?? 0],
            ['stage' => 'Приглашены', 'count' => $statusCounts[ApplicationStatus::Invited->value] ?? 0],
            ['stage' => 'Приняты', 'count' => $statusCounts[ApplicationStatus::Hired->value] ?? 0],
        ];
    }

    // ========== Employee Analytics ==========

    public function getEmployeeKpi(): array
    {
        $totalEmployees = EmployeeProfile::count();
        $activeEmployees = EmployeeProfile::active()->count();
        $totalConversations = EmployeeAiConversation::count();
        $activeConversations = EmployeeAiConversation::active()->count();
        $totalRecommendations = EmployeeAiRecommendation::count();
        $completedRecommendations = EmployeeAiRecommendation::completed()->count();
        $pendingRecommendations = EmployeeAiRecommendation::pending()->count();

        $totalDocs = EmployeeDocument::count();
        $parsedDocs = EmployeeDocument::where('status', 'parsed')->count();
        $failedDocs = EmployeeDocument::where('status', 'failed')->count();

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'total_conversations' => $totalConversations,
            'active_conversations' => $activeConversations,
            'total_recommendations' => $totalRecommendations,
            'completed_recommendations' => $completedRecommendations,
            'pending_recommendations' => $pendingRecommendations,
            'recommendation_completion_rate' => $totalRecommendations > 0
                ? round(($completedRecommendations / $totalRecommendations) * 100, 1)
                : 0,
            'total_documents' => $totalDocs,
            'parsed_documents' => $parsedDocs,
            'failed_documents' => $failedDocs,
            'doc_parse_rate' => $totalDocs > 0 ? round(($parsedDocs / $totalDocs) * 100, 1) : 0,
        ];
    }

    public function getDepartmentDistribution(): Collection
    {
        return DB::table('employee_profiles')
            ->whereNull('deleted_at')
            ->select('department', DB::raw('COUNT(*) as count'))
            ->groupBy('department')
            ->orderByDesc('count')
            ->get();
    }

    public function getContextTypes(): Collection
    {
        return DB::table('employee_ai_conversations')
            ->select('context_type', DB::raw('COUNT(*) as count'))
            ->groupBy('context_type')
            ->orderByDesc('count')
            ->get();
    }

    public function getRecommendationTypes(): Collection
    {
        return DB::table('employee_ai_recommendations')
            ->select('type', 'status', DB::raw('COUNT(*) as count'))
            ->groupBy('type', 'status')
            ->get();
    }

    /**
     * Optimized: single grouped query instead of per-day loop
     */
    public function getAiUsageTrend(int $days = 30): array
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $conversations = EmployeeAiConversation::where('created_at', '>=', $since)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $messages = EmployeeAiMessage::where('created_at', '>=', $since)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->toDateString();
            $trend[] = [
                'date' => now()->subDays($i)->format('d.m'),
                'conversations' => $conversations[$dateStr] ?? 0,
                'messages' => $messages[$dateStr] ?? 0,
            ];
        }

        return $trend;
    }

    public function getDocTypeDistribution(): Collection
    {
        return DB::table('employee_documents')
            ->select('document_type', DB::raw('COUNT(*) as count'))
            ->groupBy('document_type')
            ->orderByDesc('count')
            ->get();
    }
}
