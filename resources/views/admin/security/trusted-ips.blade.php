@extends('admin.layouts.app')

@section('title', 'Доверенные IP-адреса')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-globe me-2"></i>Доверенные IP-адреса</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <p class="text-muted mb-3">Если список не пуст, вход в админ-панель будет доступен только с указанных IP.</p>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>IP-адрес</th>
                                    <th>Метка</th>
                                    <th>Область</th>
                                    <th>Создал</th>
                                    <th>Истекает</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ips as $ip)
                                    <tr>
                                        <td class="font-monospace">{{ $ip->ip_address }}</td>
                                        <td>{{ $ip->label ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $ip->applies_to }}</span></td>
                                        <td>{{ $ip->creator->name ?? '-' }}</td>
                                        <td>{{ $ip->expires_at?->format('d.m.Y') ?? 'Бессрочно' }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.security.trusted-ips.delete', $ip) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Удалить?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            Список пуст — доступ с любого IP разрешён.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $ips->links() }}
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Добавить IP</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.security.trusted-ips.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">IP-адрес</label>
                            <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror"
                                   placeholder="192.168.1.1" value="{{ old('ip_address', request()->ip()) }}" required>
                            @error('ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Метка</label>
                            <input type="text" name="label" class="form-control" placeholder="Офис Ташкент" value="{{ old('label') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Область</label>
                            <select name="applies_to" class="form-select">
                                <option value="admin">Только админ-панель</option>
                                <option value="all">Вся система</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Срок действия</label>
                            <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                            <div class="form-text">Оставьте пустым для бессрочного.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-lg me-1"></i>Добавить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
