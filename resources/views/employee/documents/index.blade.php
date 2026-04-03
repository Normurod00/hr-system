@extends('employee.layouts.app')

@section('title', 'Мои документы')
@section('page-title', 'Мои документы')

@section('content')
<style>
    .doc-stats { display:flex; gap:16px; margin-bottom:24px; }
    .doc-stat { flex:1; padding:18px 20px; background:var(--panel); border:1px solid var(--br); border-radius:14px; display:flex; align-items:center; gap:14px; }
    .doc-stat .icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; }
    .doc-stat .info .value { font-size:22px; font-weight:800; color:var(--fg-1); }
    .doc-stat .info .label { font-size:12px; color:var(--fg-3); }

    .upload-zone { background:var(--panel); border:2px dashed var(--br); border-radius:14px; padding:24px; margin-bottom:24px; transition:all 0.2s; }
    .upload-zone:hover, .upload-zone.dragover { border-color:var(--accent); background:rgba(229,39,22,0.03); }
    .upload-zone .upload-header { display:flex; align-items:center; gap:14px; margin-bottom:16px; }
    .upload-zone .upload-header .icon { width:48px; height:48px; border-radius:12px; background:rgba(229,39,22,0.1); color:var(--accent); display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
    .upload-zone .upload-header h6 { margin:0; font-weight:700; color:var(--fg-1); }
    .upload-zone .upload-header p { margin:0; font-size:12px; color:var(--fg-3); }

    .doc-filters { display:flex; gap:10px; align-items:center; margin-bottom:20px; flex-wrap:wrap; }
    .doc-filters select, .doc-filters input { padding:9px 14px; border:1px solid var(--br); border-radius:10px; background:var(--panel); color:var(--fg-1); font-size:13px; outline:none; }
    .doc-filters select:focus, .doc-filters input:focus { border-color:var(--accent); }

    .doc-card { display:flex; align-items:center; gap:16px; padding:16px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; margin-bottom:10px; transition:all 0.2s; }
    .doc-card:hover { border-color:var(--accent); box-shadow:0 2px 12px rgba(0,0,0,0.04); }
    .doc-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
    .doc-icon.pdf { background:rgba(229,39,22,0.1); color:#E52716; }
    .doc-icon.doc { background:rgba(59,130,246,0.1); color:#3B82F6; }
    .doc-icon.img { background:rgba(139,92,246,0.1); color:#8B5CF6; }
    .doc-icon.other { background:rgba(107,114,128,0.1); color:#6B7280; }
    .doc-info { flex:1; min-width:0; }
    .doc-info .name { font-weight:600; font-size:14px; color:var(--fg-1); margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .doc-info .meta { font-size:12px; color:var(--fg-3); display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .doc-info .meta .dot { width:4px; height:4px; border-radius:50%; background:var(--fg-3); }
    .doc-status { padding:4px 12px; border-radius:8px; font-size:11px; font-weight:600; flex-shrink:0; }
    .doc-status.processed { background:rgba(34,197,94,0.12); color:#22c55e; }
    .doc-status.pending { background:rgba(245,158,11,0.12); color:#f59e0b; }
    .doc-status.error { background:rgba(239,68,68,0.12); color:#ef4444; }

    .empty-state { text-align:center; padding:60px 20px; }
    .empty-state .icon-wrap { width:80px; height:80px; border-radius:50%; background:rgba(107,114,128,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
    .empty-state .icon-wrap i { font-size:32px; color:var(--fg-3); opacity:0.5; }
</style>

@php
    $totalDocs = $documents->total();
    $typeCounts = [
        'diploma' => 'Дипломы',
        'certificate' => 'Сертификаты',
        'contract' => 'Договоры',
    ];
@endphp

{{-- Stats --}}
<div class="doc-stats">
    <div class="doc-stat">
        <div class="icon" style="background:rgba(59,130,246,0.1);color:#3B82F6;">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <div class="info">
            <div class="value">{{ $totalDocs }}</div>
            <div class="label">Всего документов</div>
        </div>
    </div>
    <div class="doc-stat">
        <div class="icon" style="background:rgba(34,197,94,0.1);color:#22c55e;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="info">
            <div class="value">{{ $documents->where('is_parsed', true)->count() }}</div>
            <div class="label">Обработано AI</div>
        </div>
    </div>
    <div class="doc-stat">
        <div class="icon" style="background:rgba(245,158,11,0.1);color:#f59e0b;">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="info">
            <div class="value">{{ $documents->where('is_parsed', false)->count() }}</div>
            <div class="label">В обработке</div>
        </div>
    </div>
</div>

{{-- Upload Zone --}}
<div class="upload-zone" id="uploadZone">
    <form action="{{ route('employee.documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        <div class="upload-header">
            <div class="icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <div>
                <h6>Загрузить документ</h6>
                <p>PDF, DOC, DOCX, TXT, JPG, PNG — до 10 МБ. AI обработка начнётся автоматически.</p>
            </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <select name="document_type" class="form-select" style="flex:0 0 200px; padding:10px 14px; border:1px solid var(--br); border-radius:10px; font-size:14px;" required>
                <option value="diploma">📜 Диплом</option>
                <option value="certificate">📋 Сертификат</option>
                <option value="contract">📄 Трудовой договор</option>
                <option value="id_document">🪪 Удостоверение</option>
                <option value="medical">🏥 Мед. справка</option>
                <option value="other">📎 Другое</option>
            </select>
            <input type="file" name="file" required accept=".pdf,.doc,.docx,.txt,.rtf,.jpg,.jpeg,.png"
                   style="flex:1; padding:10px 14px; border:1px solid var(--br); border-radius:10px; font-size:14px; background:var(--panel); color:var(--fg-1);">
            <button type="submit" style="padding:10px 24px; background:var(--accent, #E52716); color:#fff; border:none; border-radius:10px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px; transition:opacity 0.2s;">
                <i class="fa-solid fa-upload"></i> Загрузить
            </button>
        </div>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success" style="border-radius:12px; border:1px solid rgba(34,197,94,0.3); margin-bottom:16px;">
        <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
    </div>
@endif

{{-- Document List --}}
<div style="margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
    <h6 style="margin:0; font-weight:700; color:var(--fg-1);">Мои документы</h6>
</div>

@forelse($documents as $doc)
    @php
        $ext = pathinfo($doc->original_name, PATHINFO_EXTENSION);
        $iconClass = match(true) {
            in_array($ext, ['pdf']) => 'pdf',
            in_array($ext, ['doc', 'docx', 'txt', 'rtf']) => 'doc',
            in_array($ext, ['jpg', 'jpeg', 'png']) => 'img',
            default => 'other',
        };
        $iconName = match($iconClass) {
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'img' => 'fa-file-image',
            default => 'fa-file',
        };
        $statusClass = $doc->is_parsed ? 'processed' : 'pending';
        $statusText = $doc->is_parsed ? 'Обработан' : 'В обработке';
    @endphp
    <div class="doc-card">
        <div class="doc-icon {{ $iconClass }}">
            <i class="fa-solid {{ $iconName }}"></i>
        </div>
        <div class="doc-info">
            <div class="name">{{ $doc->original_name }}</div>
            <div class="meta">
                <span>{{ $doc->document_type_label ?? $doc->document_type }}</span>
                <span class="dot"></span>
                <span>{{ $doc->size_formatted ?? '' }}</span>
                <span class="dot"></span>
                <span>{{ $doc->created_at->format('d.m.Y') }}</span>
            </div>
        </div>
        <span class="doc-status {{ $statusClass }}">{{ $statusText }}</span>
    </div>
@empty
    <div class="empty-state">
        <div class="icon-wrap">
            <i class="fa-solid fa-file-circle-plus"></i>
        </div>
        <h5 style="color:var(--fg-1);">Нет документов</h5>
        <p style="color:var(--fg-3); font-size:14px;">Загрузите первый документ — AI обработает его автоматически</p>
    </div>
@endforelse

@if($documents->hasPages())
    <div style="margin-top:16px;">{{ $documents->links() }}</div>
@endif
@endsection
