<?php

namespace App\Exports;

use App\Models\EmployeeProfile;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected ?string $department;

    public function __construct(?string $department = null)
    {
        $this->department = $department;
    }

    public function query()
    {
        $query = EmployeeProfile::with('user')
            ->orderBy('department')
            ->orderBy('position');

        if ($this->department) {
            $query->where('department', $this->department);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'ФИО',
            'Email',
            'Телефон',
            'Отдел',
            'Должность',
            'Роль',
            'Дата приёма',
            'Статус',
        ];
    }

    public function map($profile): array
    {
        return [
            $profile->user_id,
            $profile->user?->name ?? '-',
            $profile->user?->email ?? '-',
            $profile->user?->phone ?? '-',
            $profile->department ?? '-',
            $profile->position ?? '-',
            $profile->employee_role?->value ?? '-',
            $profile->hire_date?->format('d.m.Y') ?? '-',
            $profile->status?->label() ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}
