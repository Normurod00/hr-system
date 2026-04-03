@extends('employee.layouts.app')

@section('title', 'Чат с HR')
@section('page-title', 'Чат с HR')

@section('content')
<style>
    .chat-stats { display:flex; gap:16px; margin-bottom:20px; }
    .chat-stat { flex:1; padding:16px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; text-align:center; }
    .chat-stat .value { font-size:24px; font-weight:800; color:var(--fg-1); }
    .chat-stat .label { font-size:12px; color:var(--fg-3); margin-top:2px; }

    .chat-list-item { display:flex; align-items:center; padding:14px 20px; background:var(--panel); border:1px solid var(--br); border-radius:12px; margin-bottom:10px; transition:all 0.2s; text-decoration:none; color:inherit; }
    .chat-list-item:hover { border-color:var(--accent); transform:translateX(4px); box-shadow:0 2px 12px rgba(0,0,0,0.06); }
    .chat-list-item.has-unread { border-left:3px solid var(--accent); }

    .chat-avatar { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:1.2rem; margin-right:16px; flex-shrink:0; position:relative; }
    .chat-avatar .online-dot { position:absolute; bottom:2px; right:2px; width:12px; height:12px; background:#22c55e; border-radius:50%; border:2px solid var(--panel); }

    .chat-info { flex:1; min-width:0; }
    .chat-name { font-weight:700; font-size:15px; color:var(--fg-1); margin-bottom:2px; display:flex; align-items:center; gap:8px; }
    .chat-name .role-tag { font-size:10px; padding:2px 8px; border-radius:6px; font-weight:600; background:rgba(59,130,246,0.15); color:#3B82F6; }
    .chat-subtitle { font-size:12px; color:var(--fg-3); margin-bottom:3px; }
    .chat-preview { font-size:13px; color:var(--fg-3); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:380px; }
    .chat-preview.unread { color:var(--fg-1); font-weight:600; }

    .chat-meta { text-align:right; flex-shrink:0; margin-left:14px; }
    .chat-time { font-size:12px; color:var(--fg-3); margin-bottom:6px; }
    .chat-unread { display:inline-flex; align-items:center; justify-content:center; min-width:24px; height:24px; background:var(--accent); color:#fff; border-radius:12px; font-size:12px; font-weight:700; padding:0 7px; }

    .empty-state { text-align:center; padding:80px 20px; }
    .empty-state .icon-wrap { width:80px; height:80px; border-radius:50%; background:rgba(59,130,246,0.1); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
    .empty-state .icon-wrap i { font-size:32px; color:#3B82F6; }
    .empty-state h5 { color:var(--fg-1); margin-bottom:6px; }
    .empty-state p { color:var(--fg-3); font-size:14px; }
</style>

@php
    $totalChats = $chats->count();
    $totalUnread = $chats->sum('unread_count');
@endphp

{{-- Stats --}}
<div class="chat-stats">
    <div class="chat-stat">
        <div class="value">{{ $totalChats }}</div>
        <div class="label">Чатов с HR</div>
    </div>
    <div class="chat-stat">
        <div class="value" style="color:{{ $totalUnread > 0 ? 'var(--accent)' : 'var(--fg-1)' }}">{{ $totalUnread }}</div>
        <div class="label">Непрочитанных</div>
    </div>
</div>

{{-- Chat List --}}
@forelse($chats as $chat)
    @php
        $unread = $chat->unread_count;
        $lastMsg = $chat->lastMessage;
        $hr = $chat->hr;
        $colors = ['#3B82F6','#8B5CF6','#E52716','#22c55e','#f59e0b','#ec4899','#06b6d4'];
        $color = $colors[($hr?->id ?? 0) % count($colors)];
    @endphp
    <a href="{{ route('employee.staff-chat.show', $chat) }}" class="chat-list-item {{ $unread > 0 ? 'has-unread' : '' }}">
        <div class="chat-avatar" style="background:{{ $color }};">
            {{ mb_strtoupper(mb_substr($hr?->name ?? '?', 0, 1)) }}
            <div class="online-dot"></div>
        </div>

        <div class="chat-info">
            <div class="chat-name">
                {{ $hr?->name ?? 'HR менеджер' }}
                <span class="role-tag">HR</span>
            </div>
            <div class="chat-subtitle">{{ $hr?->email ?? '' }}</div>
            <div class="chat-preview {{ $unread > 0 ? 'unread' : '' }}">
                @if($lastMsg)
                    @if($lastMsg->sender_id === auth()->id())
                        <span style="opacity:0.5">Вы: </span>
                    @endif
                    {{ Str::limit($lastMsg->message, 65) }}
                @else
                    <span style="opacity:0.5">Нет сообщений</span>
                @endif
            </div>
        </div>

        <div class="chat-meta">
            @if($lastMsg)
                <div class="chat-time">{{ $lastMsg->formatted_time }}</div>
            @endif
            @if($unread > 0)
                <span class="chat-unread">{{ $unread }}</span>
            @endif
        </div>
    </a>
@empty
    <div class="empty-state">
        <div class="icon-wrap">
            <i class="fa-solid fa-comments"></i>
        </div>
        <h5>Пока нет чатов</h5>
        <p>HR свяжется с вами при необходимости.<br>Здесь появятся ваши разговоры с HR-менеджерами.</p>
    </div>
@endforelse
@endsection
