<?php

namespace App\Exports;

use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ApplicationsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected ?string $status;
    protected ?int $vacancyId;

    public function __construct(?string $status = null, ?int $vacancyId = null)
    {
        $this->status = $status;
        $this->vacancyId = $vacancyId;
    }

    public function query()
    {
        $query = Application::with(['candidate', 'vacancy'])
            ->orderByDesc('created_at');

        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->vacancyId) {
            $query->where('vacancy_id', $this->vacancyId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Кандидат',
            'Email',
            'Телефон',
            'Вакансия',
            'Статус',
            'Match Score',
            'Тест (балл)',
            'Дата подачи',
        ];
    }

    public function map($app): array
    {
        return [
            $app->id,
            $app->candidate?->name ?? '-',
            $app->candidate?->email ?? '-',
            $app->candidate?->phone ?? '-',
            $app->vacancy?->title ?? '-',
            $app->status?->label() ?? $app->status,
            $app->match_score ? $app->match_score . '%' : '-',
            $app->candidateTest?->score ? $app->candidateTest->score . '%' : '-',
            $app->created_at?->format('d.m.Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
