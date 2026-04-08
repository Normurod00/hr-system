@extends('layouts.admin')

@section('title', 'Сравнение кандидатов')
@section('header', 'Сравнение кандидатов')

@push('styles')
<style>
.compare-grid {
    display: grid;
    grid-template-columns: 200px repeat({{ $applications->count() }}, 1fr);
    gap: 0;
    border: 1px solid var(--br);
    border-radius: 16px;
    overflow: hidden;
    background: var(--panel);
}

.compare-row {
    display: contents;
}

.compare-cell {
    padding: 16px 20px;
    border-bottom: 1px solid var(--br);
    border-right: 1px solid var(--br);
    font-size: 14px;
    color: var(--fg-2);
    display: flex;
    align-items: center;
    min-height: 52px;
}

.compare-cell:last-child {
    border-right: none;
}

.compare-row:last-child .compare-cell {
    border-bottom: none;
}

.compare-label {
    background: var(--grid);
    font-weight: 700;
    font-size: 13px;
    color: var(--fg-1);
}

.compare-header {
    padding: 20px;
    border-bottom: 1px solid var(--br);
    border-right: 1px solid var(--br);
    text-align: center;
    background: var(--grid);
}

.compare-header:last-child {
    border-right: none;
}

.compare-header .avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    margin: 0 auto 10px;
    object-fit: cover;
    border: 3px solid var(--panel);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.compare-header .name {
    font-size: 16px;
    font-weight: 700;
    color: var(--fg-1);
    margin-bottom: 4px;
}

.compare-header .vacancy {
    font-size: 12px;
    color: var(--fg-3);
}

.score-bar {
    width: 100%;
    height: 8px;
    background: var(--grid);
    border-radius: 4px;
    overflow: hidden;
    margin-top: 6px;
}

.score-bar__fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.6s ease;
}

.compare-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
}

.compare-best {
    background: rgba(34, 197, 94, 0.1);
    color: #16a34a;
}

.skill-tag {
    display: inline-flex;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    margin: 2px;
}

.skill-match { background: rgba(34,197,94,0.1); color: #16a34a; }
.skill-miss { background: rgba(239,68,68,0.1); color: #dc2626; }
.skill-basic { background: var(--grid); color: var(--fg-2); }

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: var(--fg-2);
    text-decoration: none;
    margin-bottom: 20px;
}

.back-link:hover { color: var(--accent); }

.winner-crown {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: rgba(245,158,11,0.1);
    color: #d97706;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    margin-top: 6px;
}
</style>
@endpush

@section('content')
<a href="{{ route('admin.applications.index') }}" class="back-link">
    <i class="bi bi-arrow-left"></i> Назад к заявкам
</a>

@php
    $maxScore = $applications->max('match_score') ?? 0;
    $maxTestScore = $applications->max(fn($a) => $a->candidateTest?->score) ?? 0;
@endphp

<div class="compare-grid">
    <!-- Header Row -->
    <div class="compare-header compare-label" style="display: flex; align-items: center; justify-content: center;">
        <span style="font-size: 14px; font-weight: 800; color: var(--fg-1);">
            <i class="bi bi-arrow-left-right me-1"></i>Сравнение
        </span>
    </div>
    @foreach($applications as $app)
        <div class="compare-header">
            <img src="{{ $app->candidate->avatar_url }}" class="avatar" alt="{{ $app->candidate->name }}">
            <div class="name">{{ $app->candidate->name }}</div>
            <div class="vacancy">{{ $app->vacancy?->title ?? '—' }}</div>
            @if($app->match_score == $maxScore && $maxScore > 0)
                <div class="winner-crown"><i class="bi bi-trophy-fill"></i> Лидер</div>
            @endif
        </div>
    @endforeach

    <!-- Match Score -->
    <div class="compare-row">
        <div class="compare-cell compare-label"><i class="bi bi-star-fill me-2" style="color: var(--accent);"></i>Match Score</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="flex-direction: column; align-items: stretch;">
                @if($app->match_score !== null)
                    @php
                        $color = $app->match_score >= 60 ? '#16a34a' : ($app->match_score >= 40 ? '#d97706' : '#dc2626');
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 24px; font-weight: 800; color: {{ $color }};">{{ $app->match_score }}%</span>
                        @if($app->match_score == $maxScore && $maxScore > 0)
                            <span class="compare-badge compare-best"><i class="bi bi-check-circle me-1"></i>Лучший</span>
                        @endif
                    </div>
                    <div class="score-bar"><div class="score-bar__fill" style="width: {{ $app->match_score }}%; background: {{ $color }};"></div></div>
                @else
                    <span style="color: var(--fg-3);">Не оценён</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Test Score -->
    <div class="compare-row">
        <div class="compare-cell compare-label"><i class="bi bi-clipboard-check me-2" style="color: var(--info);"></i>Тест</div>
        @foreach($applications as $app)
            <div class="compare-cell">
                @if($app->candidateTest && $app->candidateTest->status === 'completed')
                    @php $testColor = $app->candidateTest->score >= 60 ? '#16a34a' : ($app->candidateTest->score >= 40 ? '#d97706' : '#dc2626'); @endphp
                    <span style="font-size: 20px; font-weight: 800; color: {{ $testColor }};">{{ $app->candidateTest->score }}%</span>
                    <span style="margin-left: 8px; font-size: 12px; color: var(--fg-3);">{{ $app->candidateTest->correct_answers }}/{{ $app->candidateTest->total_questions }}</span>
                @else
                    <span style="color: var(--fg-3);">{{ $app->candidateTest ? ($app->candidateTest->status === 'in_progress' ? 'В процессе' : 'Истёк') : 'Не начат' }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Experience -->
    <div class="compare-row">
        <div class="compare-cell compare-label"><i class="bi bi-briefcase me-2" style="color: var(--info);"></i>Опыт</div>
        @foreach($applications as $app)
            <div class="compare-cell">
                @php $profile = $app->candidate?->candidateProfile; @endphp
                @if($profile && $profile->years_of_experience)
                    <span style="font-weight: 700;">{{ $profile->years_of_experience }} {{ trans_choice('год|года|лет', $profile->years_of_experience) }}</span>
                    @if($profile->has_management_experience)
                        <span class="skill-tag skill-match" style="margin-left: 8px;"><i class="bi bi-people me-1"></i>Управление</span>
                    @endif
                @else
                    <span style="color: var(--fg-3);">—</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Position -->
    <div class="compare-row">
        <div class="compare-cell compare-label"><i class="bi bi-person-badge me-2" style="color: var(--fg-3);"></i>Должность</div>
        @foreach($applications as $app)
            <div class="compare-cell">
                {{ $app->candidate?->candidateProfile?->position_title ?? '—' }}
            </div>
        @endforeach
    </div>

    <!-- Skills -->
    <div class="compare-row">
        <div class="compare-cell compare-label" style="align-items: flex-start; padding-top: 18px;"><i class="bi bi-tools me-2" style="color: #16a34a;"></i>Навыки</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="flex-wrap: wrap; align-items: flex-start;">
                @php $skills = $app->candidate?->candidateProfile?->skills ?? []; @endphp
                @if(count($skills) > 0)
                    @foreach(array_slice($skills, 0, 12) as $skill)
                        @php
                            $level = $skill['level'] ?? 'basic';
                            $cls = match($level) { 'strong' => 'skill-match', 'middle', 'medium' => 'skill-basic', default => 'skill-basic' };
                        @endphp
                        <span class="skill-tag {{ $cls }}">{{ $skill['name'] ?? $skill }}</span>
                    @endforeach
                    @if(count($skills) > 12)
                        <span class="skill-tag" style="background: var(--grid); color: var(--fg-3);">+{{ count($skills) - 12 }}</span>
                    @endif
                @else
                    <span style="color: var(--fg-3);">—</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Languages -->
    <div class="compare-row">
        <div class="compare-cell compare-label"><i class="bi bi-translate me-2" style="color: var(--info);"></i>Языки</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="flex-wrap: wrap;">
                @php $langs = $app->candidate?->candidateProfile?->languages ?? []; @endphp
                @forelse($langs as $lang)
                    <span class="skill-tag skill-basic">{{ is_array($lang) ? ($lang['name'] ?? '') : $lang }}{{ is_array($lang) && !empty($lang['level']) ? ' ('.$lang['level'].')' : '' }}</span>
                @empty
                    <span style="color: var(--fg-3);">—</span>
                @endforelse
            </div>
        @endforeach
    </div>

    <!-- Strengths -->
    <div class="compare-row">
        <div class="compare-cell compare-label" style="align-items: flex-start; padding-top: 18px;"><i class="bi bi-hand-thumbs-up me-2" style="color: #16a34a;"></i>Сильные стороны</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                @if($app->analysis && $app->analysis->strengths)
                    @foreach($app->analysis->strengths as $s)
                        <div style="font-size: 13px; line-height: 1.4;"><span style="color: #16a34a; margin-right: 4px;">+</span>{{ $s }}</div>
                    @endforeach
                @else
                    <span style="color: var(--fg-3);">Нет данных</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Weaknesses -->
    <div class="compare-row">
        <div class="compare-cell compare-label" style="align-items: flex-start; padding-top: 18px;"><i class="bi bi-hand-thumbs-down me-2" style="color: #d97706;"></i>Слабые стороны</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                @if($app->analysis && $app->analysis->weaknesses)
                    @foreach($app->analysis->weaknesses as $w)
                        <div style="font-size: 13px; line-height: 1.4;"><span style="color: #d97706; margin-right: 4px;">-</span>{{ $w }}</div>
                    @endforeach
                @else
                    <span style="color: var(--fg-3);">Нет данных</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Risks -->
    <div class="compare-row">
        <div class="compare-cell compare-label" style="align-items: flex-start; padding-top: 18px;"><i class="bi bi-exclamation-triangle me-2" style="color: #dc2626;"></i>Риски</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                @if($app->analysis && count($app->analysis->risks ?? []) > 0)
                    @foreach($app->analysis->risks as $r)
                        <div style="font-size: 13px; line-height: 1.4;"><span style="color: #dc2626; margin-right: 4px;">!</span>{{ $r }}</div>
                    @endforeach
                @else
                    <span style="color: #16a34a; font-weight: 600; font-size: 12px;"><i class="bi bi-shield-check me-1"></i>Рисков нет</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Recommendation -->
    <div class="compare-row">
        <div class="compare-cell compare-label" style="align-items: flex-start; padding-top: 18px;"><i class="bi bi-lightbulb me-2" style="color: var(--accent);"></i>AI рекомендация</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="flex-direction: column; align-items: flex-start;">
                @if($app->analysis && $app->analysis->recommendation)
                    <div style="font-size: 13px; line-height: 1.5; color: var(--fg-2);">{{ $app->analysis->recommendation }}</div>
                @else
                    <span style="color: var(--fg-3);">Нет данных</span>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Status -->
    <div class="compare-row">
        <div class="compare-cell compare-label"><i class="bi bi-flag me-2" style="color: var(--fg-3);"></i>Статус</div>
        @foreach($applications as $app)
            <div class="compare-cell">
                <span class="badge badge-{{ $app->status->value }}" style="font-weight: 700; padding: 6px 14px;">{{ $app->status_label }}</span>
            </div>
        @endforeach
    </div>

    <!-- Actions -->
    <div class="compare-row">
        <div class="compare-cell compare-label"><i class="bi bi-three-dots me-2"></i>Действия</div>
        @foreach($applications as $app)
            <div class="compare-cell" style="gap: 8px;">
                <a href="{{ route('admin.applications.show', $app) }}" class="btn btn-sm" style="background: var(--accent); color: white; font-weight: 600; border-radius: 8px; padding: 6px 14px; font-size: 12px; text-decoration: none;">
                    <i class="bi bi-eye me-1"></i>Подробнее
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
