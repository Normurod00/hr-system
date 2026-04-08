<?php

namespace App\Services\Integrations\Kpi;

use App\Models\EmployeeProfile;
use App\Services\Integrations\Contracts\KpiProviderInterface;
use Illuminate\Support\Collection;

/**
 * Mock провайдер KPI — реалистичные данные для демонстрации
 */
class MockKpiProvider implements KpiProviderInterface
{
    private array $metricsDefinitions = [
        'customer_service' => [
            'name' => 'Mijozlarga xizmat ko\'rsatish sifati',
            'description' => 'Mijozlar qoniqish darajasi (NPS/CSI)',
            'unit' => '%',
        ],
        'task_completion' => [
            'name' => 'Vazifalarni bajarish',
            'description' => 'O\'z vaqtida bajarilgan vazifalar ulushi',
            'unit' => '%',
        ],
        'sales' => [
            'name' => 'Bank mahsulotlari sotish',
            'description' => 'Kreditlar, kartalar, depozitlar sotish hajmi',
            'unit' => 'dona',
        ],
        'quality' => [
            'name' => 'Ish sifati',
            'description' => 'Bajarilgan ishlar sifat bahosi',
            'unit' => 'ball',
        ],
        'attendance' => [
            'name' => 'Ish intizomi',
            'description' => 'Kechikishlarsiz ish kunlari ulushi',
            'unit' => '%',
        ],
        'training' => [
            'name' => 'Malaka oshirish',
            'description' => 'Majburiy kurslar va treninglarni o\'tash',
            'unit' => '%',
        ],
    ];

    /**
     * Oylik KPI ma'lumotlari — har bir oy uchun qat'iy belgilangan
     */
    private array $monthlyData = [
        '2026-04' => [
            'customer_service' => ['value' => 92.5, 'target' => 90],
            'task_completion'  => ['value' => 88.0, 'target' => 95],
            'sales'            => ['value' => 47,   'target' => 50],
            'quality'          => ['value' => 4.3,  'target' => 4.5],
            'attendance'       => ['value' => 96.0, 'target' => 98],
            'training'         => ['value' => 58.0, 'target' => 100],
            'total' => 80.1,
        ],
        '2026-03' => [
            'customer_service' => ['value' => 88.0, 'target' => 90],
            'task_completion'  => ['value' => 91.0, 'target' => 95],
            'sales'            => ['value' => 52,   'target' => 50],
            'quality'          => ['value' => 4.1,  'target' => 4.5],
            'attendance'       => ['value' => 98.0, 'target' => 98],
            'training'         => ['value' => 75.0, 'target' => 100],
            'total' => 82.9,
        ],
        '2026-02' => [
            'customer_service' => ['value' => 85.0, 'target' => 90],
            'task_completion'  => ['value' => 82.0, 'target' => 95],
            'sales'            => ['value' => 38,   'target' => 50],
            'quality'          => ['value' => 3.9,  'target' => 4.5],
            'attendance'       => ['value' => 95.0, 'target' => 98],
            'training'         => ['value' => 40.0, 'target' => 100],
            'total' => 72.4,
        ],
        '2026-01' => [
            'customer_service' => ['value' => 90.0, 'target' => 90],
            'task_completion'  => ['value' => 86.0, 'target' => 95],
            'sales'            => ['value' => 45,   'target' => 50],
            'quality'          => ['value' => 4.2,  'target' => 4.5],
            'attendance'       => ['value' => 97.0, 'target' => 98],
            'training'         => ['value' => 60.0, 'target' => 100],
            'total' => 78.5,
        ],
        '2025-12' => [
            'customer_service' => ['value' => 93.0, 'target' => 90],
            'task_completion'  => ['value' => 94.0, 'target' => 95],
            'sales'            => ['value' => 55,   'target' => 50],
            'quality'          => ['value' => 4.5,  'target' => 4.5],
            'attendance'       => ['value' => 100,  'target' => 98],
            'training'         => ['value' => 100,  'target' => 100],
            'total' => 95.2,
        ],
        '2025-11' => [
            'customer_service' => ['value' => 87.0, 'target' => 90],
            'task_completion'  => ['value' => 89.0, 'target' => 95],
            'sales'            => ['value' => 42,   'target' => 50],
            'quality'          => ['value' => 4.0,  'target' => 4.5],
            'attendance'       => ['value' => 96.0, 'target' => 98],
            'training'         => ['value' => 50.0, 'target' => 100],
            'total' => 75.8,
        ],
    ];

    /**
     * Bonus jadvali — KPI ga qarab hisoblash
     */
    private array $bonusTable = [
        100 => ['multiplier' => 1.5, 'base' => 3500000],  // 5,250,000 so'm
        90  => ['multiplier' => 1.2, 'base' => 3500000],  // 4,200,000 so'm
        70  => ['multiplier' => 1.0, 'base' => 3500000],  // 3,500,000 so'm
        50  => ['multiplier' => 0.7, 'base' => 3500000],  // 2,450,000 so'm
        0   => ['multiplier' => 0,   'base' => 0],         // bonus yo'q
    ];

    public function getEmployeeKpi(
        EmployeeProfile $employee,
        string $periodType,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): ?array {
        $startDate = $startDate ?? $this->getDefaultStartDate($periodType);
        $endDate = $endDate ?? $this->getDefaultEndDate($periodType, $startDate);

        $monthKey = \Carbon\Carbon::parse($startDate)->format('Y-m');
        $data = $this->monthlyData[$monthKey] ?? $this->monthlyData['2026-04'];

        $metrics = $this->buildMetrics($data);
        $totalScore = $data['total'];

        return [
            'employee_id' => $employee->employee_number,
            'period_type' => $periodType,
            'period_start' => \Carbon\Carbon::parse($startDate)->format('Y-m-d'),
            'period_end' => \Carbon\Carbon::parse($endDate)->format('Y-m-d'),
            'metrics' => $metrics,
            'total_score' => round($totalScore, 2),
            'status' => 'approved',
            'bonus_info' => $this->calculateBonus($totalScore),
            'synced_at' => now()->toIso8601String(),
        ];
    }

    public function getEmployeeKpiHistory(EmployeeProfile $employee, int $months = 12): Collection
    {
        $history = collect();
        $currentDate = now()->startOfMonth();

        for ($i = 0; $i < min($months, count($this->monthlyData)); $i++) {
            $date = $currentDate->copy()->subMonths($i);
            $kpi = $this->getEmployeeKpi(
                $employee,
                'month',
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth()
            );

            if ($kpi) {
                $history->push($kpi);
            }
        }

        return $history;
    }

    public function getDepartmentKpi(string $department, string $periodType): ?array
    {
        return [
            'department' => $department,
            'period_type' => $periodType,
            'average_score' => 79.3,
            'employee_count' => 24,
            'top_performers' => 6,
            'needs_improvement' => 3,
        ];
    }

    public function getAvailablePeriods(): array
    {
        $periods = [];

        foreach (array_keys($this->monthlyData) as $monthKey) {
            $date = \Carbon\Carbon::parse($monthKey . '-01');
            $periods[] = [
                'type' => 'month',
                'start' => $date->startOfMonth()->format('Y-m-d'),
                'end' => $date->endOfMonth()->format('Y-m-d'),
                'label' => $date->translatedFormat('F Y'),
            ];
        }

        return $periods;
    }

    public function getMetricsDefinitions(): array
    {
        return $this->metricsDefinitions;
    }

    public function healthCheck(): array
    {
        return [
            'healthy' => true,
            'message' => 'KPI Provider is operational',
            'latency_ms' => 12,
        ];
    }

    public function syncEmployeeKpi(EmployeeProfile $employee): bool
    {
        return true;
    }

    // ===== PRIVATE HELPERS =====

    private function getDefaultStartDate(string $periodType): \DateTimeInterface
    {
        return match ($periodType) {
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    private function getDefaultEndDate(string $periodType, \DateTimeInterface $startDate): \DateTimeInterface
    {
        $start = \Carbon\Carbon::parse($startDate);

        return match ($periodType) {
            'quarter' => $start->copy()->endOfQuarter(),
            'year' => $start->copy()->endOfYear(),
            default => $start->copy()->endOfMonth(),
        };
    }

    private function buildMetrics(array $data): array
    {
        $weights = [
            'customer_service' => 0.25,
            'task_completion'  => 0.20,
            'sales'            => 0.20,
            'quality'          => 0.15,
            'attendance'       => 0.10,
            'training'         => 0.10,
        ];

        $metrics = [];

        foreach ($this->metricsDefinitions as $key => $definition) {
            if (!isset($data[$key])) continue;

            $value = $data[$key]['value'];
            $target = $data[$key]['target'];
            $completion = $target > 0 ? round(min(120, ($value / $target) * 100), 2) : 0;

            $metrics[$key] = [
                'name' => $definition['name'],
                'value' => $value,
                'target' => $target,
                'weight' => $weights[$key] ?? 0.10,
                'unit' => $definition['unit'],
                'completion' => $completion,
            ];
        }

        return $metrics;
    }

    private function calculateBonus(float $totalScore): array
    {
        foreach ($this->bonusTable as $threshold => $info) {
            if ($totalScore >= $threshold) {
                $amount = $info['base'] * $info['multiplier'];

                if ($amount <= 0) {
                    return [
                        'eligible' => false,
                        'amount' => 0,
                        'paid' => false,
                        'reason' => 'KPI minimal chegaradan past (50%)',
                    ];
                }

                return [
                    'eligible' => true,
                    'amount' => (int) $amount,
                    'paid' => $totalScore >= 70,
                    'multiplier' => $info['multiplier'],
                    'currency' => 'UZS',
                ];
            }
        }

        return ['eligible' => false, 'amount' => 0, 'paid' => false];
    }
}
