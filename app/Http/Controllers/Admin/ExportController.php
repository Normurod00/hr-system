<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ApplicationsExport;
use App\Exports\EmployeesExport;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\RecognitionAward;
use App\Models\Vacancy;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * PDF: Отчёт по кандидату
     */
    public function candidatePdf(Application $application)
    {
        $application->load(['candidate.candidateProfile', 'vacancy', 'analysis', 'candidateTest']);

        $profile = $application->candidate?->candidateProfile;
        $analysis = $application->analysis;

        $pdf = Pdf::loadView('exports.pdf.candidate-report', compact('application', 'profile', 'analysis'))
            ->setPaper('a4')
            ->setOption(['defaultFont' => 'DejaVu Sans']);

        $filename = 'candidate_' . ($application->candidate?->name ?? $application->id) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * PDF: Воронка рекрутинга
     */
    public function funnelPdf(Request $request)
    {
        $dateFrom = Carbon::parse($request->input('from', now()->startOfMonth()));
        $dateTo = Carbon::parse($request->input('to', now()));

        $baseQuery = Application::whereBetween('created_at', [$dateFrom, $dateTo]);

        $funnel = [
            'new' => (clone $baseQuery)->count(),
            'in_review' => (clone $baseQuery)->where('status', '!=', 'new')->count(),
            'invited' => (clone $baseQuery)->whereIn('status', ['invited', 'hired'])->count(),
            'hired' => (clone $baseQuery)->where('status', 'hired')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
        ];

        // Статистика по вакансиям
        $vacancyStats = Vacancy::withCount([
            'applications as total' => fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]),
            'applications as invited' => fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])->whereIn('status', ['invited', 'hired']),
            'applications as hired' => fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])->where('status', 'hired'),
        ])
            ->withAvg(['applications as avg_score' => fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo])], 'match_score')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->take(20)
            ->get()
            ->map(fn($v) => [
                'title' => $v->title,
                'total' => $v->total,
                'invited' => $v->invited,
                'hired' => $v->hired,
                'avg_score' => $v->avg_score,
            ])
            ->toArray();

        $pdf = Pdf::loadView('exports.pdf.funnel-report', compact('funnel', 'vacancyStats', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'DejaVu Sans']);

        return $pdf->download('funnel_' . $dateFrom->format('Ymd') . '_' . $dateTo->format('Ymd') . '.pdf');
    }

    /**
     * PDF: Сертификат награды
     */
    public function awardCertificate(RecognitionAward $award)
    {
        $award->load(['employee.employeeProfile']);

        $pdf = Pdf::loadView('exports.pdf.award-certificate', compact('award'))
            ->setPaper('a4', 'landscape')
            ->setOption(['defaultFont' => 'DejaVu Sans']);

        return $pdf->download('certificate_' . $award->id . '.pdf');
    }

    /**
     * Excel: Заявки
     */
    public function applicationsExcel(Request $request)
    {
        return Excel::download(
            new ApplicationsExport(
                $request->input('status'),
                $request->input('vacancy_id')
            ),
            'applications_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    /**
     * Excel: Сотрудники
     */
    public function employeesExcel(Request $request)
    {
        return Excel::download(
            new EmployeesExport($request->input('department')),
            'employees_' . now()->format('Ymd_His') . '.xlsx'
        );
    }
}
