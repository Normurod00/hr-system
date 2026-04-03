@extends('employee.layouts.app')

@section('title', 'AI Ассистент')
@section('page-title', 'AI Ассистент')

@section('content')
<style>
    .ai-hero { display:flex; gap:20px; margin-bottom:24px; padding:24px; background:linear-gradient(135deg, rgba(229,39,22,0.06), rgba(139,92,246,0.06)); border:1px solid var(--br); border-radius:16px; }
    .ai-hero .icon-wrap { width:64px; height:64px; border-radius:16px; background:linear-gradient(135deg, #E52716, #8B5CF6); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .ai-hero .icon-wrap i { font-size:1.5rem; color:#fff; }
    .ai-hero .text h5 { margin:0 0 4px; font-weight:700; color:var(--fg-1); }
    .ai-hero .text p { margin:0; font-size:14px; color:var(--fg-3); line-height:1.5; }

    .quick-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px; margin-bottom:24px; }
    .quick-card { padding:16px; background:var(--panel); border:1px solid var(--br); border-radius:12px; cursor:pointer; transition:all 0.2s; text-decoration:none; color:inherit; display:flex; align-items:center; gap:12px; }
    .quick-card:hover { border-color:var(--accent); transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,0.06); }
    .quick-card .qicon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
    .quick-card .qtext { font-size:13px; font-weight:500; color:var(--fg-1); line-height:1.3; }

    .new-conv-card { background:var(--panel); border:1px solid var(--br); border-radius:14px; padding:24px; margin-bottom:24px; }
    .new-conv-card h6 { font-weight:700; color:var(--fg-1); margin-bottom:16px; }
    .new-conv-card .form-row { display:flex; gap:12px; flex-wrap:wrap; }
    .new-conv-card select { padding:10px 14px; border:1px solid var(--br); border-radius:10px; background:var(--panel); color:var(--fg-1); font-size:14px; flex:0 0 200px; }
    .new-conv-card textarea { flex:1; min-width:300px; padding:12px 16px; border:1px solid var(--br); border-radius:10px; background:var(--panel); color:var(--fg-1); font-size:14px; resize:none; outline:none; min-height:48px; }
    .new-conv-card textarea:focus { border-color:var(--accent); }
    .new-conv-card button[type=submit] { padding:10px 24px; background:var(--accent); color:#fff; border:none; border-radius:10px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px; white-space:nowrap; }

    .conv-list { background:var(--panel); border:1px solid var(--br); border-radius:14px; overflow:hidden; }
    .conv-list .header { padding:16px 20px; border-bottom:1px solid var(--br); display:flex; justify-content:space-between; align-items:center; }
    .conv-list .header h6 { margin:0; font-weight:700; color:var(--fg-1); }

    .conv-item { display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid var(--br); text-decoration:none; color:inherit; transition:all 0.15s; }
    .conv-item:last-child { border-bottom:none; }
    .conv-item:hover { background:rgba(0,0,0,0.02); }
    .conv-item .cicon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
    .conv-item .cinfo { flex:1; min-width:0; }
    .conv-item .cinfo .title { font-weight:600; font-size:14px; color:var(--fg-1); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .conv-item .cinfo .meta { font-size:12px; color:var(--fg-3); display:flex; align-items:center; gap:8px; margin-top:2px; }
    .conv-item .time { font-size:12px; color:var(--fg-3); flex-shrink:0; }
    .conv-item .arrow { color:var(--fg-3); opacity:0.3; }

    .context-colors { --general: #6B7280; --kpi: #3B82F6; --leave: #22c55e; --bonus: #f59e0b; --policy: #8B5CF6; --complaint: #ef4444; }

    .empty-state { text-align:center; padding:60px 20px; }
    .empty-state i { font-size:48px; opacity:0.15; display:block; margin-bottom:12px; }
</style>

<div class="context-colors">

{{-- Hero --}}
<div class="ai-hero">
    <div class="icon-wrap"><i class="fa-solid fa-robot"></i></div>
    <div class="text">
        <h5>AI Ассистент</h5>
        <p>Задайте любой вопрос — по KPI, отпуску, бонусам, политикам банка. AI проанализирует ваши данные и даст персональный ответ.</p>
    </div>
</div>

{{-- Quick Questions --}}
<div class="quick-grid">
    <div class="quick-card" onclick="quickAsk('kpi', 'Почему мой KPI за этот месяц ниже, чем в прошлом?')">
        <div class="qicon" style="background:rgba(59,130,246,0.1);color:#3B82F6;"><i class="fa-solid fa-chart-line"></i></div>
        <div class="qtext">Почему упал KPI?</div>
    </div>
    <div class="quick-card" onclick="quickAsk('bonus', 'Когда будет начислен бонус за текущий квартал?')">
        <div class="qicon" style="background:rgba(34,197,94,0.1);color:#22c55e;"><i class="fa-solid fa-coins"></i></div>
        <div class="qtext">Когда будет бонус?</div>
    </div>
    <div class="quick-card" onclick="quickAsk('leave', 'Сколько дней отпуска у меня осталось?')">
        <div class="qicon" style="background:rgba(245,158,11,0.1);color:#f59e0b;"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="qtext">Остаток отпуска</div>
    </div>
    <div class="quick-card" onclick="quickAsk('policy', 'Какой порядок оформления больничного листа?')">
        <div class="qicon" style="background:rgba(139,92,246,0.1);color:#8B5CF6;"><i class="fa-solid fa-book"></i></div>
        <div class="qtext">Правила больничного</div>
    </div>
</div>

{{-- New Conversation --}}
<div class="new-conv-card">
    <h6><i class="fa-solid fa-plus-circle me-2" style="color:var(--accent);"></i>Новый разговор</h6>
    <form id="newConversationForm" class="form-row">
        @csrf
        <select name="context_type">
            <option value="general">💬 Общий вопрос</option>
            <option value="kpi">📊 KPI</option>
            <option value="leave">🏖️ Отпуск</option>
            <option value="bonus">💰 Бонусы</option>
            <option value="policy">📋 Политики</option>
        </select>
        <textarea name="message" placeholder="Задайте ваш вопрос..." required rows="1"></textarea>
        <button type="submit"><i class="fa-solid fa-paper-plane"></i> Отправить</button>
    </form>
</div>

{{-- Conversations List --}}
<div class="conv-list">
    <div class="header">
        <h6><i class="fa-solid fa-clock-rotate-left me-2" style="color:var(--accent);"></i>История разговоров</h6>
        <span style="font-size:12px; padding:4px 10px; background:var(--br); border-radius:8px; color:var(--fg-3);">{{ $conversations->total() }}</span>
    </div>

    @forelse($conversations as $conv)
        @php
            $contextColors = ['general'=>'#6B7280','kpi'=>'#3B82F6','leave'=>'#22c55e','bonus'=>'#f59e0b','policy'=>'#8B5CF6','complaint'=>'#ef4444'];
            $contextIcons = ['general'=>'fa-comment','kpi'=>'fa-chart-line','leave'=>'fa-calendar','bonus'=>'fa-coins','policy'=>'fa-book','complaint'=>'fa-flag'];
            $ctx = $conv->context_type->value ?? 'general';
            $color = $contextColors[$ctx] ?? '#6B7280';
            $icon = $contextIcons[$ctx] ?? 'fa-comment';
        @endphp
        <a href="{{ route('employee.chat.show', $conv) }}" class="conv-item">
            <div class="cicon" style="background:{{ $color }}15; color:{{ $color }};">
                <i class="fa-solid {{ $icon }}"></i>
            </div>
            <div class="cinfo">
                <div class="title">{{ $conv->display_title }}</div>
                <div class="meta">
                    <span style="color:{{ $color }};">{{ $conv->context_label }}</span>
                    <span>·</span>
                    <span>{{ $conv->message_count }} сообщений</span>
                    @if($conv->status->value === 'active')
                        <span style="color:#22c55e;">● Активен</span>
                    @endif
                </div>
            </div>
            <span class="time">{{ $conv->last_message_at?->diffForHumans() ?? $conv->created_at->diffForHumans() }}</span>
            <i class="fa-solid fa-chevron-right arrow"></i>
        </a>
    @empty
        <div class="empty-state">
            <i class="fa-solid fa-robot" style="color:var(--fg-3);"></i>
            <h5 style="color:var(--fg-1);">Нет разговоров</h5>
            <p style="color:var(--fg-3); font-size:14px;">Задайте первый вопрос AI-ассистенту</p>
        </div>
    @endforelse
</div>

@if($conversations->hasPages())
    <div style="margin-top:16px;">{{ $conversations->links() }}</div>
@endif

</div>
@endsection

@push('scripts')
<script>
    function quickAsk(context, message) {
        document.querySelector('[name="context_type"]').value = context;
        const textarea = document.querySelector('[name="message"]');
        textarea.value = message;
        textarea.focus();
        textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    document.getElementById('newConversationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const formData = new FormData(this);
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Отправка...';

        try {
            const res = await fetch('{{ route("employee.chat.store") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ context_type: formData.get('context_type'), message: formData.get('message') }),
            });
            if (!res.ok) throw new Error((await res.json().catch(() => ({}))).message || 'Ошибка');
            const data = await res.json();
            if (data.redirect_url) window.location.href = data.redirect_url;
            else throw new Error('Не удалось создать');
        } catch (err) {
            alert(err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Отправить';
        }
    });
</script>
@endpush
