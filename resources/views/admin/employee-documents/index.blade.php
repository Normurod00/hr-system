@extends('layouts.admin')

@section('title', 'Документы сотрудников — AI анализ')

@push('styles')
<style>
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-in { animation: fadeSlideUp 0.45s ease both; }
    .animate-in:nth-child(2) { animation-delay: 0.05s; }
    .animate-in:nth-child(3) { animation-delay: 0.1s; }
    .animate-in:nth-child(4) { animation-delay: 0.15s; }
    .animate-in:nth-child(5) { animation-delay: 0.2s; }
    .animate-in:nth-child(6) { animation-delay: 0.25s; }

    .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px; flex-wrap: wrap; gap: 16px;
    }
    .page-header h1 {
        font-size: 28px; font-weight: 800; color: var(--fg-1); margin: 0;
        display: flex; align-items: center; gap: 14px;
    }
    .page-header .header-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 20px;
        box-shadow: 0 4px 14px rgba(20,184,166,0.3);
    }
    .upload-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 22px; border-radius: 12px; border: none;
        background: var(--accent); color: #fff;
        font-size: 14px; font-weight: 600; cursor: pointer;
        box-shadow: 0 2px 10px rgba(229,39,22,0.25);
        transition: all 0.2s;
    }
    .upload-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(229,39,22,0.35); }

    /* KPI */
    .kpi-grid {
        display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-bottom: 28px;
    }
    @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }

    .kpi-card {
        background: var(--panel); border: 1px solid var(--br); border-radius: 16px;
        padding: 20px; position: relative; overflow: hidden; transition: all 0.3s ease;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.07); border-color: transparent; }
    .kpi-card__accent { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 16px 16px 0 0; }
    .kpi-card__icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; margin-bottom: 14px;
    }
    .kpi-card__icon.blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
    .kpi-card__icon.green { background: rgba(34,197,94,0.12); color: #22c55e; }
    .kpi-card__icon.orange { background: rgba(245,158,11,0.12); color: #f59e0b; }
    .kpi-card__icon.cyan { background: rgba(6,182,212,0.12); color: #06b6d4; }
    .kpi-card__icon.red { background: rgba(239,68,68,0.12); color: #ef4444; }
    .kpi-card__icon.teal { background: rgba(20,184,166,0.12); color: #14b8a6; }

    .kpi-card__value { font-size: 32px; font-weight: 800; color: var(--fg-1); line-height: 1; margin-bottom: 4px; }
    .kpi-card__value .unit { font-size: 18px; color: var(--fg-3); font-weight: 600; }
    .kpi-card__label { font-size: 13px; color: var(--fg-3); font-weight: 500; }

    /* Cards */
    .a-card {
        background: var(--panel); border: 1px solid var(--br); border-radius: 16px;
        overflow: hidden; transition: box-shadow 0.3s;
    }
    .a-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .a-card__header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px; border-bottom: 1px solid var(--br);
    }
    .a-card__title {
        font-size: 16px; font-weight: 700; color: var(--fg-1);
        display: flex; align-items: center; gap: 10px;
    }
    .a-card__title i {
        width: 32px; height: 32px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(229,39,22,0.08); color: var(--accent); font-size: 14px;
    }
    .a-card__body { padding: 20px 24px; }
    .a-card__body--flush { padding: 0; }

    /* Table */
    .a-card .table { margin: 0; }
    .a-card .table th {
        background: transparent; border-bottom: 1px solid var(--br);
        font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px;
        color: var(--fg-3); font-weight: 600; padding: 14px 20px; white-space: nowrap;
    }
    .a-card .table td {
        padding: 14px 20px; vertical-align: middle;
        border-bottom: 1px solid var(--br); color: var(--fg-1); font-size: 14px;
    }
    .a-card .table tbody tr:last-child td { border-bottom: none; }
    .a-card .table tbody tr { transition: background 0.15s; }
    .a-card .table tbody tr:hover { background: var(--grid); }

    /* Filter card */
    .filter-card {
        background: var(--panel); border: 1px solid var(--br); border-radius: 16px;
        padding: 20px 24px; margin-bottom: 24px;
    }
    .filter-card label { font-size: 12px; font-weight: 600; color: var(--fg-3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .filter-card select {
        background: var(--grid); border: 1px solid var(--br); border-radius: 10px;
        padding: 9px 14px; font-size: 14px; color: var(--fg-1); width: 100%;
        transition: border-color 0.2s;
    }
    .filter-card select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(229,39,22,0.1); }
    .filter-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 9px 18px; border-radius: 10px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .filter-btn--primary { background: var(--accent); color: #fff; }
    .filter-btn--primary:hover { box-shadow: 0 2px 8px rgba(229,39,22,0.3); }
    .filter-btn--ghost { background: var(--grid); color: var(--fg-2); border: 1px solid var(--br); text-decoration: none; }
    .filter-btn--ghost:hover { background: var(--panel); color: var(--fg-1); }

    /* Type badge */
    .type-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 12px; border-radius: 10px;
        font-size: 13px; font-weight: 600;
        background: var(--grid); color: var(--fg-2); border: 1px solid var(--br);
    }
    .type-badge i { font-size: 14px; }

    /* Status pill */
    .status-pill {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 12px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
    }
    .status-pill--pending { background: rgba(245,158,11,0.12); color: #d97706; }
    .status-pill--processing { background: rgba(6,182,212,0.12); color: #0891b2; }
    .status-pill--parsed { background: rgba(34,197,94,0.12); color: #16a34a; }
    .status-pill--failed { background: rgba(239,68,68,0.12); color: #dc2626; }

    /* Action buttons */
    .action-btn {
        width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--br);
        background: var(--panel); color: var(--fg-3);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 14px; cursor: pointer; transition: all 0.2s;
    }
    .action-btn:hover { border-color: var(--accent); color: var(--accent); background: rgba(229,39,22,0.04); }
    .action-btn--danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.04); }
    .action-btn--warn:hover { border-color: #f59e0b; color: #f59e0b; background: rgba(245,158,11,0.04); }

    .empty-state { text-align: center; padding: 56px 20px; color: var(--fg-3); }
    .empty-state i { font-size: 48px; opacity: 0.2; margin-bottom: 12px; display: block; }
    .empty-state p { margin: 0; font-size: 15px; }

    .analytics-grid { display: grid; gap: 24px; margin-bottom: 24px; }
    .analytics-grid--1-1 { grid-template-columns: 1fr 1fr; }
    @media (max-width: 900px) { .analytics-grid--1-1 { grid-template-columns: 1fr; } }

    /* Modal override */
    .modal-content { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
    .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--br); background: var(--grid); border-radius: 16px 16px 0 0; }
    .modal-header .modal-title { font-size: 18px; font-weight: 700; color: var(--fg-1); display: flex; align-items: center; gap: 10px; }
    .modal-header .modal-title i { color: var(--accent); }
    .modal-body { padding: 24px; }
    .modal-body label { font-size: 13px; font-weight: 600; color: var(--fg-2); margin-bottom: 6px; }
    .modal-body select, .modal-body input[type="file"] {
        background: var(--grid); border: 1px solid var(--br); border-radius: 10px;
        padding: 10px 14px; font-size: 14px; color: var(--fg-1);
    }
    .modal-body select:focus, .modal-body input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(229,39,22,0.1); }
    .modal-footer { padding: 16px 24px; border-top: 1px solid var(--br); }
    .modal-footer .btn-secondary { background: var(--grid); border: 1px solid var(--br); color: var(--fg-2); border-radius: 10px; font-weight: 600; }
    .modal-footer .btn-primary { background: var(--accent); border: none; border-radius: 10px; font-weight: 600; box-shadow: 0 2px 8px rgba(229,39,22,0.25); }

    .pagination { gap: 4px; }
    .pagination .page-link { border-radius: 8px; border: 1px solid var(--br); color: var(--fg-2); font-size: 13px; font-weight: 600; }
    .pagination .page-item.active .page-link { background: var(--accent); border-color: var(--accent); }
</style>
@endpush

@section('content')
<div class="page-header animate-in">
    <h1>
        <span class="header-icon"><i class="fa-solid fa-file-shield"></i></span>
        Документы сотрудников
    </h1>
    <button type="button" class="upload-btn" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="fa-solid fa-cloud-arrow-up"></i> Загрузить документ
    </button>
</div>

<!-- KPI -->
<div class="kpi-grid">
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
        <div class="kpi-card__icon blue"><i class="fa-solid fa-files"></i></div>
        <div class="kpi-card__value">{{ $kpi['total'] }}</div>
        <div class="kpi-card__label">Всего</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #22c55e, #4ade80);"></div>
        <div class="kpi-card__icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="kpi-card__value" style="color: #16a34a;">{{ $kpi['parsed'] }}</div>
        <div class="kpi-card__label">Обработано</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
        <div class="kpi-card__icon orange"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="kpi-card__value" style="color: #d97706;">{{ $kpi['pending'] }}</div>
        <div class="kpi-card__label">Ожидают</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #06b6d4, #67e8f9);"></div>
        <div class="kpi-card__icon cyan"><i class="fa-solid fa-gear fa-spin-slow"></i></div>
        <div class="kpi-card__value" style="color: #0891b2;">{{ $kpi['processing'] }}</div>
        <div class="kpi-card__label">В обработке</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #ef4444, #fca5a5);"></div>
        <div class="kpi-card__icon red"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="kpi-card__value" style="color: {{ $kpi['failed'] > 0 ? '#dc2626' : 'var(--fg-3)' }};">{{ $kpi['failed'] }}</div>
        <div class="kpi-card__label">Ошибки</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #14b8a6, #5eead4);"></div>
        <div class="kpi-card__icon teal"><i class="fa-solid fa-percent"></i></div>
        <div class="kpi-card__value" style="color: {{ $kpi['success_rate'] >= 80 ? '#16a34a' : ($kpi['success_rate'] >= 50 ? '#d97706' : '#dc2626') }};">{{ $kpi['success_rate'] }}<span class="unit">%</span></div>
        <div class="kpi-card__label">Успешность</div>
    </div>
</div>

<!-- Charts -->
<div class="analytics-grid analytics-grid--1-1 animate-in">
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-chart-pie"></i> По типу документа</div>
        </div>
        <div class="a-card__body"><canvas id="typeChart" height="240"></canvas></div>
    </div>
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-chart-pie"></i> По статусу</div>
        </div>
        <div class="a-card__body"><canvas id="statusChart" height="240"></canvas></div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card animate-in">
    <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 16px; align-items: end;">
        <div>
            <label>Статус</label>
            <select name="status">
                <option value="">Все статусы</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Ожидает</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>В обработке</option>
                <option value="parsed" {{ request('status') === 'parsed' ? 'selected' : '' }}>Обработан</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Ошибка</option>
            </select>
        </div>
        <div>
            <label>Тип документа</label>
            <select name="document_type">
                <option value="">Все типы</option>
                <option value="contract" {{ request('document_type') === 'contract' ? 'selected' : '' }}>Трудовой договор</option>
                <option value="diploma" {{ request('document_type') === 'diploma' ? 'selected' : '' }}>Диплом</option>
                <option value="certificate" {{ request('document_type') === 'certificate' ? 'selected' : '' }}>Сертификат</option>
                <option value="id_document" {{ request('document_type') === 'id_document' ? 'selected' : '' }}>Удостоверение</option>
                <option value="medical" {{ request('document_type') === 'medical' ? 'selected' : '' }}>Медицинская</option>
                <option value="other" {{ request('document_type') === 'other' ? 'selected' : '' }}>Другое</option>
            </select>
        </div>
        <div>
            <label>Сотрудник</label>
            <select name="employee_id">
                <option value="">Все сотрудники</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->user->name ?? $emp->employee_number }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 8px; padding-bottom: 1px;">
            <button type="submit" class="filter-btn filter-btn--primary"><i class="fa-solid fa-search"></i> Найти</button>
            <a href="{{ route('admin.employee-documents.index') }}" class="filter-btn filter-btn--ghost"><i class="fa-solid fa-xmark"></i></a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="a-card animate-in" style="margin-bottom: 24px;">
    <div class="a-card__header">
        <div class="a-card__title"><i class="fa-solid fa-table-list"></i> Документы</div>
        <span style="font-size: 13px; color: var(--fg-3);">{{ $documents->total() }} записей</span>
    </div>
    <div class="a-card__body--flush">
        <table class="table">
            <thead>
                <tr>
                    <th>Сотрудник</th>
                    <th>Тип</th>
                    <th>Файл</th>
                    <th>Размер</th>
                    <th>Статус</th>
                    <th>Обработан</th>
                    <th style="text-align:right">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                <tr>
                    <td><strong>{{ $doc->employeeProfile?->user?->name ?? '—' }}</strong></td>
                    <td>
                        <span class="type-badge">
                            <i class="bi {{ $doc->document_type_icon }}"></i>{{ $doc->document_type_label }}
                        </span>
                    </td>
                    <td style="font-size: 13px;">{{ Str::limit($doc->original_name, 28) }}</td>
                    <td style="font-size: 13px; color: var(--fg-3);">{{ $doc->size_formatted }}</td>
                    <td>
                        <span class="status-pill status-pill--{{ $doc->status }}">
                            @if($doc->status === 'processing')<i class="fa-solid fa-spinner fa-spin" style="font-size: 10px;"></i>@endif
                            {{ $doc->status_label }}
                        </span>
                    </td>
                    <td style="font-size: 13px; color: var(--fg-3);">{{ $doc->processed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                            <a href="{{ route('admin.employee-documents.show', $doc) }}" class="action-btn" title="Просмотр">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form action="{{ route('admin.employee-documents.reprocess', $doc) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="action-btn action-btn--warn" title="Переобработать">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.employee-documents.destroy', $doc) }}" method="POST" style="display:inline;" onsubmit="return confirm('Удалить документ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn--danger" title="Удалить">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-folder-open"></i>
                            <p>Документы не найдены</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--br); display: flex; justify-content: center;">
        {{ $documents->links() }}
    </div>
    @endif
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.employee-documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-cloud-arrow-up"></i> Загрузить документ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Сотрудник</label>
                        <select name="employee_profile_id" class="form-select" required>
                            <option value="">— Выберите сотрудника —</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? $emp->employee_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Тип документа</label>
                        <select name="document_type" class="form-select" required>
                            <option value="contract">Трудовой договор</option>
                            <option value="diploma">Диплом</option>
                            <option value="certificate">Сертификат</option>
                            <option value="id_document">Удостоверение личности</option>
                            <option value="medical">Медицинская справка</option>
                            <option value="other">Другое</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Файл</label>
                        <input type="file" name="file" class="form-control" required accept=".pdf,.doc,.docx,.txt,.rtf,.jpg,.jpeg,.png">
                        <div style="font-size: 12px; color: var(--fg-3); margin-top: 6px;">PDF, DOC, DOCX, TXT, RTF, JPG, PNG. Максимум 10 МБ.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i> Загрузить</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif';
Chart.defaults.font.size = 12;
Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--fg-3').trim() || '#888';

const typeData = @json($typeDistribution);
const typeLabels = {contract:'Договор',diploma:'Диплом',certificate:'Сертификат',id_document:'Удостоверение',medical:'Медицинская',other:'Другое'};
const typeColors = ['#3b82f6','#8b5cf6','#22c55e','#f59e0b','#ec4899','#6b7280','#14b8a6'];

new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: typeData.map(d => typeLabels[d.document_type] || d.document_type),
        datasets: [{ data: typeData.map(d => d.count), backgroundColor: typeColors.slice(0, typeData.length), borderWidth: 0, hoverOffset: 10, spacing: 2 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } },
            tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10 } }
    }
});

const statusData = @json($statusDistribution);
const sColors = {pending:'#f59e0b',processing:'#06b6d4',parsed:'#22c55e',failed:'#ef4444'};
const sLabels = {pending:'Ожидает',processing:'Обработка',parsed:'Обработан',failed:'Ошибка'};

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusData.map(d => sLabels[d.status] || d.status),
        datasets: [{ data: statusData.map(d => d.count), backgroundColor: statusData.map(d => sColors[d.status] || '#6b7280'), borderWidth: 0, hoverOffset: 10, spacing: 2 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } },
            tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10 } }
    }
});
</script>
@endpush
