@extends('layouts.admin')

@section('title', 'Чат — ' . $chat->employee->name)

@section('content')
<style>
    .chat-window { display:flex; flex-direction:column; height:calc(100vh - 140px); background:var(--panel); border:1px solid var(--br); border-radius:16px; overflow:hidden; }

    .chat-header { display:flex; align-items:center; gap:14px; padding:16px 24px; border-bottom:1px solid var(--br); }
    .chat-header .back-btn { width:38px; height:38px; border-radius:10px; border:1px solid var(--br); background:transparent; color:var(--fg-3); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; text-decoration:none; }
    .chat-header .back-btn:hover { border-color:var(--accent); color:var(--accent); }
    .chat-header .avatar { width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:1.1rem; flex-shrink:0; position:relative; }
    .chat-header .avatar .online { position:absolute; bottom:1px; right:1px; width:12px; height:12px; background:#22c55e; border-radius:50%; border:2px solid var(--panel); }
    .chat-header .info { flex:1; }
    .chat-header .info h6 { margin:0; font-weight:700; font-size:15px; color:var(--fg-1); }
    .chat-header .info small { color:var(--fg-3); font-size:12px; }
    .chat-header .role-tag { font-size:11px; padding:4px 12px; border-radius:8px; font-weight:600; flex-shrink:0; }

    .chat-messages { flex:1; overflow-y:auto; padding:24px; display:flex; flex-direction:column; gap:10px; background:rgba(0,0,0,0.01); }

    .msg { display:flex; max-width:70%; }
    .msg.mine { align-self:flex-end; }
    .msg.theirs { align-self:flex-start; }
    .msg .bubble { padding:12px 18px; border-radius:18px; font-size:14px; line-height:1.5; word-break:break-word; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
    .msg.mine .bubble { background:var(--accent); color:#fff; border-bottom-right-radius:4px; }
    .msg.theirs .bubble { background:var(--panel); border:1px solid var(--br); color:var(--fg-1); border-bottom-left-radius:4px; }
    .msg .time { font-size:11px; color:var(--fg-3); margin-top:4px; padding:0 6px; }
    .msg.mine .time { text-align:right; }

    .chat-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; }
    .chat-empty i { font-size:52px; opacity:0.1; color:var(--fg-3); margin-bottom:12px; }
    .chat-empty p { color:var(--fg-3); font-size:14px; }

    .chat-input { display:flex; gap:12px; padding:16px 24px; border-top:1px solid var(--br); }
    .chat-input input { flex:1; padding:14px 20px; border:1px solid var(--br); border-radius:14px; background:transparent; color:var(--fg-1); font-size:14px; outline:none; transition:border 0.2s; }
    .chat-input input:focus { border-color:var(--accent); }
    .chat-input input::placeholder { color:var(--fg-3); }
    .chat-input button { width:52px; height:52px; border-radius:14px; border:none; background:var(--accent); color:#fff; font-size:1.2rem; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
    .chat-input button:hover { opacity:0.9; transform:scale(1.03); }
</style>

@php
    $colors = ['#E52716','#3B82F6','#8B5CF6','#22c55e','#f59e0b','#ec4899','#06b6d4'];
    $color = $colors[($chat->employee?->id ?? 0) % count($colors)];
    $empProfile = $chat->employee?->employeeProfile;
@endphp

<div class="chat-window">
    {{-- Header --}}
    <div class="chat-header">
        <a href="{{ route('admin.staff-chat.index') }}" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="avatar" style="background:{{ $color }};">
            {{ mb_strtoupper(mb_substr($chat->employee->name, 0, 1)) }}
            <div class="online"></div>
        </div>
        <div class="info">
            <h6>{{ $chat->employee->name }}</h6>
            <small>{{ $chat->employee->email }}{{ $empProfile?->position ? ' · ' . $empProfile->position : '' }}</small>
        </div>
        <span class="role-tag" style="background:rgba({{ $chat->employee->role === 'hr' ? '59,130,246' : ($chat->employee->role === 'admin' ? '229,39,22' : '107,114,128') }},0.12); color:{{ $chat->employee->role === 'hr' ? '#3B82F6' : ($chat->employee->role === 'admin' ? '#E52716' : 'var(--fg-3)') }};">
            {{ $chat->employee->role === 'hr' ? 'HR' : ($chat->employee->role === 'admin' ? 'Admin' : 'Сотрудник') }}
        </span>
    </div>

    {{-- Messages --}}
    <div class="chat-messages" id="messagesContainer">
        @forelse($messages as $msg)
            <div class="msg {{ $msg->sender_id === auth()->id() ? 'mine' : 'theirs' }}">
                <div>
                    <div class="bubble">{{ $msg->message }}</div>
                    <div class="time">{{ $msg->formatted_time }}</div>
                </div>
            </div>
        @empty
            <div class="chat-empty">
                <i class="fa-solid fa-paper-plane"></i>
                <p>Начните общение с {{ $chat->employee->name }}</p>
            </div>
        @endforelse
    </div>

    {{-- Input --}}
    <form id="messageForm" class="chat-input">
        <input type="text" id="messageInput" placeholder="Напишите сообщение..." autocomplete="off" autofocus>
        <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
</div>

<script>
const CHAT_ID = @json($chat->id);
const MY_ID = @json(auth()->id());
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
let lastMessageId = @json($messages->last()?->id ?? 0);

const container = document.getElementById('messagesContainer');
container.scrollTop = container.scrollHeight;

document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';

    appendMessage({ message: text, formatted_time: 'Сейчас', is_mine: true });

    try {
        const res = await fetch(`/admin/staff-chat/${CHAT_ID}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        if (data.message) lastMessageId = data.message.id;
    } catch (e) {
        console.error('Send error:', e);
    }
});

function appendMessage(msg) {
    const empty = container.querySelector('.chat-empty');
    if (empty) empty.remove();

    const div = document.createElement('div');
    div.className = 'msg ' + (msg.is_mine ? 'mine' : 'theirs');
    div.innerHTML = `<div><div class="bubble">${escapeHtml(msg.message)}</div><div class="time">${msg.formatted_time}</div></div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

if (typeof Echo !== 'undefined') {
    Echo.private(`staff-chat.${CHAT_ID}`)
        .listen('.message.sent', (e) => {
            if (e.senderId === MY_ID) return;
            appendMessage({ message: e.body, formatted_time: 'Сейчас', is_mine: false });
            lastMessageId = e.messageId;
        });
} else {
    setInterval(async () => {
        try {
            const res = await fetch(`/admin/staff-chat/${CHAT_ID}/messages?last_id=${lastMessageId}`);
            const { messages } = await res.json();
            messages.forEach(m => {
                if (!m.is_mine) appendMessage(m);
                lastMessageId = m.id;
            });
        } catch(e) {}
    }, 3000);
}
</script>
@endsection
