@extends('layouts.admin')

@section('title', 'Чат — ' . $chat->employee->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm" style="height: calc(100vh - 160px); display: flex; flex-direction: column;">
                {{-- Header --}}
                <div class="card-header d-flex align-items-center gap-3">
                    <a href="{{ route('admin.staff-chat.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                         style="width:40px;height:40px;background:linear-gradient(135deg,#E52716,#ff6b5b);">
                        {{ mb_strtoupper(mb_substr($chat->employee->name, 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-0">{{ $chat->employee->name }}</h6>
                        <small class="text-muted">{{ $chat->employee->email }}</small>
                    </div>
                </div>

                {{-- Messages --}}
                <div class="card-body overflow-auto" id="messagesContainer" style="flex: 1;">
                    @forelse($messages as $msg)
                        <div class="d-flex mb-3 {{ $msg->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            <div style="max-width: 70%;">
                                <div class="px-3 py-2 rounded-3 {{ $msg->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}">
                                    {{ $msg->message }}
                                </div>
                                <small class="text-muted d-block {{ $msg->sender_id === auth()->id() ? 'text-end' : '' }}" style="font-size:0.75rem;">
                                    {{ $msg->formatted_time }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-text" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-2">Начните общение</p>
                        </div>
                    @endforelse
                </div>

                {{-- Input --}}
                <div class="card-footer">
                    <form id="messageForm" class="d-flex gap-2">
                        <input type="text" id="messageInput" class="form-control" placeholder="Напишите сообщение..." autocomplete="off" autofocus>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const CHAT_ID = @json($chat->id);
const MY_ID = @json(auth()->id());
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
let lastMessageId = @json($messages->last()?->id ?? 0);

const container = document.getElementById('messagesContainer');
container.scrollTop = container.scrollHeight;

// Send message
document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text) return;

    input.value = '';

    // Optimistic UI
    appendMessage({ message: text, sender_name: 'Вы', formatted_time: 'Сейчас', is_mine: true });

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
    const div = document.createElement('div');
    div.className = 'd-flex mb-3 ' + (msg.is_mine ? 'justify-content-end' : 'justify-content-start');
    div.innerHTML = `
        <div style="max-width:70%;">
            <div class="px-3 py-2 rounded-3 ${msg.is_mine ? 'bg-primary text-white' : 'bg-light'}">
                ${escapeHtml(msg.message)}
            </div>
            <small class="text-muted d-block ${msg.is_mine ? 'text-end' : ''}" style="font-size:0.75rem;">
                ${msg.formatted_time}
            </small>
        </div>
    `;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Real-time via Echo (if available)
if (typeof Echo !== 'undefined') {
    Echo.private(`staff-chat.${CHAT_ID}`)
        .listen('.message.sent', (e) => {
            if (e.senderId === MY_ID) return;
            appendMessage({
                message: e.body,
                sender_name: e.senderName,
                formatted_time: 'Сейчас',
                is_mine: false,
            });
            lastMessageId = e.messageId;
        });
} else {
    // Polling fallback
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
