<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\AiLog;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getStats(): array
    {
        $statusCounts = Application::query()
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'vacancies_active' => Vacancy::active()->count(),
            'vacancies_total' => Vacancy::count(),
            'applications_total' => array_sum($statusCounts),
            'applications_new' => $statusCounts[ApplicationStatus::New->value] ?? 0,
            'applications_in_review' => $statusCounts[ApplicationStatus::InReview->value] ?? 0,
            'applications_invited' => $statusCounts[ApplicationStatus::Invited->value] ?? 0,
            'applications_hired' => $statusCounts[ApplicationStatus::Hired->value] ?? 0,
            'applications_rejected' => $statusCounts[ApplicationStatus::Rejected->value] ?? 0,
            'candidates_total' => User::where('role', 'candidate')->count(),
        ];
    }

    public function getWeeklyChanges(): array
    {
        $lastWeekApplications = Application::where('created_at', '>=', now()->subWeeks(2))
            ->where('created_at', '<', now()->subWeek())
            ->count();
        $thisWeekApplications = Application::where('created_at', '>=', now()->subWeek())->count();

        $lastWeekHired = Application::where('status', ApplicationStatus::Hired)
            ->where('updated_at', '>=', now()->subWeeks(2))
            ->where('updated_at', '<', now()->subWeek())
            ->count();
        $thisWeekHired = Application::where('status', ApplicationStatus::Hired)
            ->where('updated_at', '>=', now()->subWeek())
            ->count();

        return [
            'applications' => $this->calculateChange($lastWeekApplications, $thisWeekApplications),
            'hired' => $this->calculateChange($lastWeekHired, $thisWeekHired),
        ];
    }

    public function getRecentApplications(int $limit = 8): Collection
    {
        return Application::query()
            ->with(['candidate', 'vacancy'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getPopularVacancies(int $limit = 5): Collection
    {
        return Vacancy::query()
            ->active()
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->take($limit)
            ->get();
    }

    public function getAiStats(): array
    {
        $since = now()->subDay();

        $stats = AiLog::where('created_at', '>=', $since)
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success, SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors")
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'success' => $stats->success ?? 0,
            'errors' => $stats->errors ?? 0,
        ];
    }

    public function getApplicationsChartData(int $days = 14): array
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $counts = Application::where('created_at', '>=', $since)
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $data = [];
        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d.m');
            $data[] = $counts[$date->toDateString()] ?? 0;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function getStatusChartData(array $stats): array
    {
        return [
            ['status' => 'Новые', 'count' => $stats['applications_new'], 'color' => '#3b82f6'],
            ['status' => 'На рассмотрении', 'count' => $stats['applications_in_review'], 'color' => '#f59e0b'],
            ['status' => 'Приглашены', 'count' => $stats['applications_invited'], 'color' => '#8b5cf6'],
            ['status' => 'Приняты', 'count' => $stats['applications_hired'], 'color' => '#22c55e'],
            ['status' => 'Отклонены', 'count' => $stats['applications_rejected'], 'color' => '#ef4444'],
        ];
    }

    public function getRecentActivity(int $limit = 10): array
    {
        $activities = [];

        $recentApps = Application::with(['candidate', 'vacancy'])
            ->latest()
            ->take(5)
            ->get();

        foreach ($recentApps as $app) {
            $activities[] = [
                'type' => 'application',
                'icon' => 'fa-file-lines',
                'color' => 'info',
                'title' => $app->candidate?->name ?? 'Кандидат',
                'description' => 'подал заявку на "' . ($app->vacancy?->title ?? 'вакансию') . '"',
                'time' => $app->created_at,
            ];
        }

        $statusChanges = Application::with(['candidate', 'vacancy'])
            ->where('status', '!=', ApplicationStatus::New)
            ->where('updated_at', '>=', now()->subDay())
            ->latest('updated_at')
            ->take(5)
            ->get();

        foreach ($statusChanges as $app) {
            $statusLabel = $app->status->label();
            $icon = match ($app->status) {
                ApplicationStatus::Hired => 'fa-user-check',
                ApplicationStatus::Rejected => 'fa-user-xmark',
                ApplicationStatus::Invited => 'fa-envelope',
                ApplicationStatus::InReview => 'fa-magnifying-glass',
                default => 'fa-circle',
            };
            $color = match ($app->status) {
                ApplicationStatus::Hired => 'success',
                ApplicationStatus::Rejected => 'danger',
                ApplicationStatus::Invited => 'purple',
                ApplicationStatus::InReview => 'warning',
                default => 'secondary',
            };

            $activities[] = [
                'type' => 'status',
                'icon' => $icon,
                'color' => $color,
                'title' => $app->candidate?->name ?? 'Кандидат',
                'description' => "статус изменён на \"{$statusLabel}\"",
                'time' => $app->updated_at,
            ];
        }

        usort($activities, fn($a, $b) => $b['time']->timestamp - $a['time']->timestamp);

        return array_slice($activities, 0, $limit);
    }

    public function getKanbanColumns(): array
    {
        return [
            ['key' => ApplicationStatus::New->value, 'title' => 'Новые', 'color' => '#3b82f6'],
            ['key' => ApplicationStatus::InReview->value, 'title' => 'На рассмотрении', 'color' => '#f59e0b'],
            ['key' => ApplicationStatus::Invited->value, 'title' => 'Приглашены', 'color' => '#8b5cf6'],
            ['key' => ApplicationStatus::Rejected->value, 'title' => 'Отклонены', 'color' => '#ef4444'],
            ['key' => ApplicationStatus::Hired->value, 'title' => 'Приняты', 'color' => '#22c55e'],
        ];
    }

    public function getKanbanApplications(int $limit = 40): Collection
    {
        $applications = Application::with([
            'candidate',
            'candidate.candidateProfile',
            'vacancy',
            'latestAnalysis',
        ])
            ->latest()
            ->take($limit)
            ->get();

        return $applications->map(function (Application $app) {
            $profile = $app->candidate?->candidateProfile;
            $analysis = $app->latestAnalysis;
            $contactInfo = $profile?->contact_info ?? [];

            return [
                'id' => $app->id,
                'status' => $app->status->value,
                'status_label' => $app->status_label,
                'name' => $app->candidate?->name ?? 'Кандидат',
                'vacancy' => $app->vacancy?->title ?? '—',
                'match_score' => $app->match_score,
                'position_title' => $profile?->position_title ?? null,
                'strong_skills' => $profile?->getStrongSkills() ?? [],
                'domains' => $profile?->domains ?? [],
                'contact' => [
                    'email' => $app->candidate?->email ?? ($contactInfo['email'] ?? null),
                    'phone' => $app->candidate?->phone ?? ($contactInfo['phone'] ?? null),
                    'pin' => $app->candidate?->pin ?? null,
                ],
                'analysis' => [
                    'strengths' => $analysis?->strengths ?? [],
                    'weaknesses' => $analysis?->weaknesses ?? [],
                    'risks' => $analysis?->risks ?? [],
                    'questions' => $analysis?->suggested_questions ?? [],
                    'recommendation' => $analysis?->recommendation ?? '',
                ],
            ];
        });
    }

    private function calculateChange(int $old, int $new): array
    {
        if ($old === 0) {
            return ['value' => $new > 0 ? 100 : 0, 'direction' => 'up'];
        }

        $change = round((($new - $old) / $old) * 100, 1);

        return [
            'value' => abs($change),
            'direction' => $change >= 0 ? 'up' : 'down',
        ];
    }
}
