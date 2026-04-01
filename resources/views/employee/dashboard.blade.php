@extends('employee.layouts.app')

@section('title', 'Дашборд')
@section('page-title', 'Добро пожаловать, ' . auth()->user()->name)

@push('styles')
<style>
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim { animation: fadeSlideUp 0.45s ease both; }
    .anim:nth-child(2) { animation-delay: 0.06s; }
    .anim:nth-child(3) { animation-delay: 0.12s; }
    .anim:nth-child(4) { animation-delay: 0.18s; }

    .kpi-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; margin-bottom: 28px; }
    @media (max-width: 1100px) { .kpi-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: 16px; padding: 24px;
        position: relative; overflow: hidden; transition: all 0.3s ease;
    }
    .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.07); border-color: transparent; }
    .kpi-card__bar { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 16px 16px 0 0; }
    .kpi-card__head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; }
    .kpi-card__icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .kpi-card__icon.red { background: rgba(214,0,28,0.1); color: var(--brb-primary); }
    .kpi-card__icon.blue { background: rgba(0,102,255,0.1); color: var(--brb-info); }
    .kpi-card__icon.orange { background: rgba(255,149,0,0.1); color: var(--brb-warning); }
    .kpi-card__icon.green { background: rgba(0,168,107,0.1); color: var(--brb-success); }

    .kpi-card__badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .kpi-card__badge.up { background: rgba(0,168,107,0.1); color: var(--brb-success); }
    .kpi-card__badge.down { background: rgba(214,0,28,0.1); color: var(--brb-primary); }
    .kpi-card__badge.neutral { background: #f0f0f0; color: #6c757d; }

    .kpi-card__val { font-size: 36px; font-weight: 800; color: var(--brb-dark); line-height: 1; margin-bottom: 6px; }
    .kpi-card__val .u { font-size: 20px; color: #6c757d; font-weight: 600; }
    .kpi-card__lbl { font-size: 14px; color: #6c757d; font-weight: 500; }
    .kpi-card__meta { margin-top: 14px; padding-top: 14px; border-top: 1px solid #e9ecef; font-size: 13px; color: #6c757d; }

    .section-divider {
        display: flex; align-items: center; gap: 12px; margin: 8px 0 20px;
    }
    .section-divider__icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .section-divider__icon.chart { background: rgba(214,0,28,0.1); color: var(--brb-primary); }
    .section-divider__icon.rec { background: rgba(255,149,0,0.1); color: var(--brb-warning); }
    .section-divider__icon.team { background: rgba(0,102,255,0.1); color: var(--brb-info); }
    .section-divider__text { font-size: 18px; font-weight: 700; color: var(--brb-dark); }
    .section-divider__line { flex: 1; height: 1px; background: #e9ecef; }

    .d-card {
        background: #fff; border: 1px solid #e9ecef; border-radius: 16px;
        overflow: hidden; transition: box-shadow 0.3s;
    }
    .d-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
    .d-card__head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px; border-bottom: 1px solid #e9ecef;
    }
    .d-card__title { font-size: 16px; font-weight: 700; color: var(--brb-dark); display: flex; align-items: center; gap: 10px; }
    .d-card__title i { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: rgba(214,0,28,0.08); color: var(--brb-primary); font-size: 14px; }
    .d-card__body { padding: 20px 24px; }
    .d-card__body--flush { padding: 0; }

    .d-grid { display: grid; gap: 24px; margin-bottom: 24px; }
    .d-grid--2-1 { grid-template-columns: 2fr 1fr; }
    .d-grid--1-1 { grid-template-columns: 1fr 1fr; }
    @media (max-width: 900px) { .d-grid--2-1, .d-grid--1-1 { grid-template-columns: 1fr; } }

    .action-grid { display: grid; gap: 10px; }
    .action-btn {
        display: flex; align-items: center; gap: 12px;
        padding: 14px 18px; border-radius: 12px;
        background: #fff; border: 1px solid #e9ecef;
        color: var(--brb-dark); font-weight: 600; font-size: 14px;
        text-decoration: none; transition: all 0.2s;
    }
    .action-btn:hover { border-color: var(--brb-primary); color: var(--brb-primary); transform: translateX(4px); }
    .action-btn i { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .action-btn .icon-red { background: rgba(214,0,28,0.08); color: var(--brb-primary); }
    .action-btn .icon-blue { background: rgba(0,102,255,0.08); color: var(--brb-info); }
    .action-btn .icon-green { background: rgba(0,168,107,0.08); color: var(--brb-success); }

    .rec-item {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 16px 24px; border-bottom: 1px solid #e9ecef; transition: background 0.15s;
    }
    .rec-item:last-child { border-bottom: none; }
    .rec-item:hover { background: #fafafa; }
    .rec-priority {
        width: 28px; height: 28px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0;
    }

    .conv-item {
        display: flex; align-items: center; gap: 14px;
        padding: 16px 24px; border-bottom: 1px solid #e9ecef;
        text-decoration: none; color: inherit; transition: background 0.15s;
    }
    .conv-item:last-child { border-bottom: none; }
    .conv-item:hover { background: #fafafa; }
    .conv-icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: #f0f0f0; font-size: 18px; color: #6c757d; flex-shrink: 0;
    }

    .team-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
    @media (max-width: 768px) { .team-grid { grid-template-columns: repeat(2,1fr); } }
    .team-stat { text-align: center; padding: 20px; background: #fafafa; border-radius: 12px; }
    .team-stat__val { font-size: 32px; font-weight: 800; line-height: 1; margin-bottom: 4px; }
    .team-stat__lbl { font-size: 13px; color: #6c757d; }

    .empty-state { text-align: center; padding: 40px 20px; color: #6c757d; }
    .empty-state i { font-size: 36px; opacity: 0.25; display: block; margin-bottom: 8px; }

    .tip-card {
        margin-top: 16px; padding: 14px 18px; border-radius: 12px;
        background: rgba(214,0,28,0.06); border: 1px solid rgba(214,0,28,0.15);
        font-size: 13px; color: var(--brb-primary); display: flex; align-items: center; gap: 10px;
    }
</style>
@endpush

@section('content')
@if(!$employee)
    <div class="d-card anim" style="margin-top: 20px;">
        <div class="d-card__body" style="text-align: center; padding: 60px 20px;">
            <i class="bi bi-person-exclamation" style="font-size: 48px; color: var(--brb-warning); opacity: 0.6;"></i>
            <h4 style="margin-top: 16px; color: var(--brb-dark);">Профиль не найден</h4>
            <p style="color: #6c757d;">Ваш профиль сотрудника ещё не создан. Обратитесь в отдел HR.</p>
        </div>
    </div>
@else

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card anim">
        <div class="kpi-card__bar" style="background: linear-gradient(90deg, var(--brb-primary), #ff4d4d);"></div>
        <div class="kpi-card__head">
            <div class="kpi-card__icon red"><i class="bi bi-graph-up-arrow"></i></div>
            @if(isset($dashboard['risk']['delta']))
                <span class="kpi-card__badge {{ $dashboard['risk']['delta'] >= 0 ? 'up' : 'down' }}">
                    <i class="bi bi-arrow-{{ $dashboard['risk']['delta'] >= 0 ? 'up' : 'down' }}-short"></i>
                    {{ abs($dashboard['risk']['delta']) }}%
                </span>
            @endif
        </div>
        <div class="kpi-card__val">{{ number_format($dashboard['current']['total_score'] ?? 0, 1) }}<span class="u">%</span></div>
        <div class="kpi-card__lbl">Текущий KPI</div>
        <div class="kpi-card__meta">Месячная оценка эффективности</div>
    </div>

    <div class="kpi-card anim">
        <div class="kpi-card__bar" style="background: linear-gradient(90deg, var(--brb-info), #66b3ff);"></div>
        <div class="kpi-card__head">
            <div class="kpi-card__icon blue"><i class="bi bi-calendar3"></i></div>
            <span class="kpi-card__badge neutral">{{ $dashboard['quarter']['period_label'] ?? 'Q' }}</span>
        </div>
        <div class="kpi-card__val">{{ number_format($dashboard['quarter']['total_score'] ?? 0, 1) }}<span class="u">%</span></div>
        <div class="kpi-card__lbl">Квартальный KPI</div>
    </div>

    <div class="kpi-card anim">
        <div class="kpi-card__bar" style="background: linear-gradient(90deg, var(--brb-warning), #ffcc00);"></div>
        <div class="kpi-card__head">
            <div class="kpi-card__icon orange"><i class="bi bi-lightbulb"></i></div>
        </div>
        <div class="kpi-card__val">{{ $activeRecommendations->count() }}</div>
        <div class="kpi-card__lbl">Рекомендации</div>
        <div class="kpi-card__meta">Активных к выполнению</div>
    </div>

    <div class="kpi-card anim">
        <div class="kpi-card__bar" style="background: linear-gradient(90deg, var(--brb-success), #33d68f);"></div>
        <div class="kpi-card__head">
            <div class="kpi-card__icon green"><i class="bi bi-shield-check"></i></div>
        </div>
        @php $riskLevel = $dashboard['risk']['level'] ?? 'none'; @endphp
        <div class="kpi-card__val" style="color: {{ match($riskLevel) { 'high'=>'var(--brb-primary)', 'medium'=>'var(--brb-warning)', 'low'=>'var(--brb-info)', default=>'var(--brb-success)' } }};">
            {{ match($riskLevel) { 'high'=>'Высокий', 'medium'=>'Средний', 'low'=>'Низкий', default=>'Норма' } }}
        </div>
        <div class="kpi-card__lbl">Риск-индикатор</div>
        <div class="kpi-card__meta">{{ $dashboard['risk']['message'] ?? 'Показатели в норме' }}</div>
    </div>
</div>

<!-- Chart + Quick Actions -->
<div class="section-divider anim">
    <div class="section-divider__icon chart"><i class="bi bi-graph-up"></i></div>
    <div class="section-divider__text">Динамика</div>
    <div class="section-divider__line"></div>
</div>

<div class="d-grid d-grid--2-1 anim">
    <div class="d-card">
        <div class="d-card__head">
            <div class="d-card__title"><i class="bi bi-graph-up"></i> Динамика KPI</div>
            <a href="{{ route('employee.kpi.index') }}" class="btn btn-sm btn-outline-brb">Подробнее</a>
        </div>
        <div class="d-card__body"><canvas id="kpiTrendChart" height="240"></canvas></div>
    </div>
    <div class="d-card">
        <div class="d-card__head">
            <div class="d-card__title"><i class="bi bi-lightning"></i> Быстрые действия</div>
        </div>
        <div class="d-card__body">
            <div class="action-grid">
                <a href="{{ route('employee.chat.index') }}" class="action-btn">
                    <i class="icon-red"><i class="bi bi-chat-dots"></i></i>
                    Задать вопрос AI
                </a>
                <a href="{{ route('employee.kpi.index') }}" class="action-btn">
                    <i class="icon-blue"><i class="bi bi-graph-up"></i></i>
                    Посмотреть KPI
                </a>
                <a href="{{ route('employee.policies.index') }}" class="action-btn">
                    <i class="icon-green"><i class="bi bi-file-text"></i></i>
                    Найти политику
                </a>
                <a href="{{ route('employee.documents.index') }}" class="action-btn">
                    <i class="icon-blue"><i class="bi bi-file-earmark-medical"></i></i>
                    Мои документы
                </a>
            </div>
            @if(isset($dashboard['current']) && ($dashboard['current']['total_score'] ?? 100) < 70)
                <div class="tip-card">
                    <i class="bi bi-lightbulb-fill"></i>
                    <span><strong>Совет:</strong> Ваш KPI ниже 70%. Спросите AI, как его улучшить.</span>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Recommendations & Conversations -->
<div class="section-divider anim">
    <div class="section-divider__icon rec"><i class="bi bi-stars"></i></div>
    <div class="section-divider__text">Рекомендации и AI</div>
    <div class="section-divider__line"></div>
</div>

<div class="d-grid d-grid--1-1 anim">
    <div class="d-card">
        <div class="d-card__head">
            <div class="d-card__title"><i class="bi bi-lightbulb"></i> Рекомендации</div>
            @if($activeRecommendations->isNotEmpty() && isset($dashboard['current']['id']))
                <a href="{{ route('employee.kpi.recommendations', $dashboard['current']['id']) }}" style="font-size: 13px; font-weight: 600; color: var(--brb-primary); text-decoration: none;">Все</a>
            @endif
        </div>
        <div class="d-card__body--flush">
            @forelse($activeRecommendations as $rec)
            <div class="rec-item">
                <div class="rec-priority" style="background: {{ $rec->type_color ?? 'var(--brb-primary)' }};">{{ $rec->priority }}</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: var(--brb-dark); margin-bottom: 4px;">{{ Str::limit($rec->action, 80) }}</div>
                    <div style="font-size: 13px; color: #6c757d; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="padding: 2px 8px; border-radius: 6px; background: #f0f0f0; font-size: 11px; font-weight: 600;">{{ $rec->type_label }}</span>
                        @if($rec->expected_impact)
                            <span>+{{ $rec->expected_impact }}% к KPI</span>
                        @endif
                    </div>
                </div>
                <span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: {{ $rec->status->color() ?? '#6c757d' }}18; color: {{ $rec->status->color() ?? '#6c757d' }}; white-space: nowrap;">
                    {{ $rec->status_label }}
                </span>
            </div>
            @empty
            <div class="empty-state"><i class="bi bi-check-circle"></i>Нет активных рекомендаций</div>
            @endforelse
        </div>
    </div>

    <div class="d-card">
        <div class="d-card__head">
            <div class="d-card__title"><i class="bi bi-chat-dots"></i> Недавние разговоры</div>
            <a href="{{ route('employee.chat.index') }}" style="font-size: 13px; font-weight: 600; color: var(--brb-primary); text-decoration: none;">Все</a>
        </div>
        <div class="d-card__body--flush">
            @forelse($recentConversations as $conv)
            <a href="{{ route('employee.chat.show', $conv) }}" class="conv-item">
                <div class="conv-icon"><i class="bi {{ $conv->context_type->icon() }}"></i></div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: var(--brb-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $conv->display_title }}</div>
                    <div style="font-size: 13px; color: #6c757d;">{{ $conv->context_label }} · {{ $conv->last_message_at?->diffForHumans() }}</div>
                </div>
                <span style="padding: 4px 10px; border-radius: 8px; background: #f0f0f0; font-size: 12px; font-weight: 600; color: #6c757d; white-space: nowrap;">{{ $conv->message_count }}</span>
            </a>
            @empty
            <div class="empty-state"><i class="bi bi-chat"></i>Начните разговор с AI</div>
            @endforelse
        </div>
    </div>
</div>

@if($teamStats)
<!-- Team Stats -->
<div class="section-divider anim">
    <div class="section-divider__icon team"><i class="bi bi-people"></i></div>
    <div class="section-divider__text">Моя команда</div>
    <div class="section-divider__line"></div>
</div>

<div class="d-card anim" style="margin-bottom: 24px;">
    <div class="d-card__body">
        <div class="team-grid">
            <div class="team-stat">
                <div class="team-stat__val" style="color: var(--brb-info);">{{ $teamStats['team_size'] }}</div>
                <div class="team-stat__lbl">Сотрудников</div>
            </div>
            <div class="team-stat">
                <div class="team-stat__val" style="color: var(--brb-success);">{{ number_format($teamStats['average_score'], 1) }}%</div>
                <div class="team-stat__lbl">Средний KPI</div>
            </div>
            <div class="team-stat">
                <div class="team-stat__val" style="color: var(--brb-info);">{{ count($teamStats['top_performers']) }}</div>
                <div class="team-stat__lbl">Лидеры</div>
            </div>
            <div class="team-stat">
                <div class="team-stat__val" style="color: var(--brb-warning);">{{ count($teamStats['needs_attention']) }}</div>
                <div class="team-stat__lbl">Требуют внимания</div>
            </div>
        </div>
    </div>
</div>
@endif

@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = 'Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif';
Chart.defaults.font.size = 12;

const trendData = @json($dashboard ? ($dashboard['trend'] ?? []) : []);

if (trendData.length > 0 && document.getElementById('kpiTrendChart')) {
    const ctx = document.getElementById('kpiTrendChart').getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 240);
    grad.addColorStop(0, 'rgba(214,0,28,0.18)');
    grad.addColorStop(1, 'rgba(214,0,28,0.01)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.map(t => t.period || t.label),
            datasets: [{
                label: 'KPI',
                data: trendData.map(t => t.score),
                borderColor: '#D6001C',
                backgroundColor: grad,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: '#D6001C',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                borderWidth: 2.5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10, titleFont: { weight: '700' },
                    callbacks: { label: ctx => ctx.parsed.y.toFixed(1) + '%' } }
            },
            scales: {
                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' }, grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
