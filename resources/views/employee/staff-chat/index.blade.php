@extends('employee.layouts.app')

@section('title', 'Чат с HR')

@section('content')
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="bi bi-chat-dots me-2"></i>Чат с HR</h4>

    @if($chats->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-chat-text" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="text-muted mt-2">У вас пока нет чатов с HR. HR свяжется с вами при необходимости.</p>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="list-group list-group-flush">
                @foreach($chats as $chat)
                    <a href="{{ route('employee.staff-chat.show', $chat) }}"
                       class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <div class="position-relative">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                 style="width:48px;height:48px;background:linear-gradient(135deg,#3B82F6,#60A5FA);">
                                {{ mb_strtoupper(mb_substr($chat->hr->name, 0, 1)) }}
                            </div>
                            @if($chat->unread_count > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.7rem;">
                                    {{ $chat->unread_count }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0 {{ $chat->unread_count > 0 ? 'fw-bold' : '' }}">{{ $chat->hr->name }}</h6>
                                @if($chat->lastMessage)
                                    <small class="text-muted">{{ $chat->lastMessage->formatted_time }}</small>
                                @endif
                            </div>
                            <small class="text-muted">
                                @if($chat->lastMessage)
                                    {{ Str::limit($chat->lastMessage->message, 60) }}
                                @else
                                    HR менеджер
                                @endif
                            </small>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
