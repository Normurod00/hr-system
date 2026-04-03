@extends('admin.layouts.app')

@section('title', 'Попытки входа')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Попытки входа</h5>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="email" class="form-control form-control-sm" placeholder="Email..." value="{{ request('email') }}">
                <select name="status" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Все</option>
                    <option value="success" @selected(request('status') === 'success')>Успешные</option>
                    <option value="failed" @selected(request('status') === 'failed')>Неудачные</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Фильтр</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Время</th>
                            <th>Email</th>
                            <th>IP</th>
                            <th>Статус</th>
                            <th>Причина</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attempts as $attempt)
                            <tr class="{{ $attempt->success ? '' : 'table-danger' }}">
                                <td class="text-nowrap">{{ $attempt->created_at->format('d.m.Y H:i:s') }}</td>
                                <td>{{ $attempt->email }}</td>
                                <td class="font-monospace">{{ $attempt->ip_address }}</td>
                                <td>
                                    @if($attempt->success)
                                        <span class="badge bg-success">Успешно</span>
                                    @else
                                        <span class="badge bg-danger">Неудачно</span>
                                    @endif
                                </td>
                                <td>{{ $attempt->failure_reason ?? '-' }}</td>
                                <td class="text-truncate" style="max-width: 200px;" title="{{ $attempt->user_agent }}">
                                    {{ Str::limit($attempt->user_agent, 40) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">Нет записей</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $attempts->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
