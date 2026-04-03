@extends('layouts.admin')

@section('title', 'Чат с сотрудниками')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-chat-dots me-2"></i>Чат с сотрудниками</h4>
    </div>

    <div class="row">
        {{-- Список сотрудников --}}
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Все сотрудники</h6>
                        <input type="text" id="searchEmployee" class="form-control form-control-sm" style="max-width: 250px;" placeholder="Поиск по имени...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="employeeList">
                        @foreach($employees as $emp)
                            @php
                                $chat = $chats->get($emp->id);
                                $unread = $chat ? $chat->unread_count : 0;
                                $lastMsg = $chat?->lastMessage;
                            @endphp
                            <a href="{{ $chat ? route('admin.staff-chat.show', $chat) : route('admin.staff-chat.start', $emp) }}"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 employee-item"
                               data-name="{{ mb_strtolower($emp->name) }}">
                                <div class="position-relative">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                         style="width:48px;height:48px;background:linear-gradient(135deg,#E52716,#ff6b5b);font-size:1.1rem;">
                                        {{ mb_strtoupper(mb_substr($emp->name, 0, 1)) }}
                                    </div>
                                    @if($unread > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.7rem;">
                                            {{ $unread }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0 text-truncate {{ $unread > 0 ? 'fw-bold' : '' }}">{{ $emp->name }}</h6>
                                        @if($lastMsg)
                                            <small class="text-muted ms-2 text-nowrap">{{ $lastMsg->formatted_time }}</small>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted text-truncate">
                                            @if($lastMsg)
                                                {{ Str::limit($lastMsg->message, 50) }}
                                            @else
                                                {{ $emp->email }}
                                            @endif
                                        </small>
                                        <small class="text-muted text-nowrap ms-2">
                                            <span class="badge bg-{{ $emp->role === 'hr' ? 'primary' : ($emp->role === 'admin' ? 'danger' : 'secondary') }}">
                                                {{ $emp->role }}
                                            </span>
                                        </small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('searchEmployee').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.employee-item').forEach(item => {
        item.style.display = item.dataset.name.includes(q) ? '' : 'none';
    });
});
</script>
@endsection
