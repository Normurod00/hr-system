@extends('admin.layouts.app')

@section('title', 'Двухфакторная аутентификация')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Двухфакторная аутентификация (2FA)</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($isEnabled)
                        {{-- 2FA включён --}}
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                            <div>
                                <strong>2FA включена</strong><br>
                                Ваш аккаунт защищён двухфакторной аутентификацией.
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.security.2fa.disable') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Для отключения введите текущий пароль</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-shield-x me-1"></i>Отключить 2FA
                            </button>
                        </form>

                    @elseif($qrCodeSvg)
                        {{-- QR код сгенерирован, ждём подтверждения --}}
                        <p>Отсканируйте QR-код в приложении <strong>Google Authenticator</strong> или <strong>Authy</strong>:</p>

                        <div class="text-center my-4">
                            <div class="d-inline-block p-3 bg-white rounded shadow-sm">
                                {!! $qrCodeSvg !!}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Или введите секретный ключ вручную:</label>
                            <div class="input-group">
                                <input type="text" class="form-control font-monospace" value="{{ $secret }}" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $secret }}')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>

                        @if($recoveryCodes)
                            <div class="alert alert-warning">
                                <strong>Коды восстановления</strong> — сохраните их в надёжном месте. Каждый код можно использовать один раз:
                                <div class="font-monospace mt-2">
                                    @foreach($recoveryCodes as $code)
                                        <span class="badge bg-dark me-1 mb-1">{{ $code }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.security.2fa.confirm') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Введите 6-значный код из приложения для подтверждения:</label>
                                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                       maxlength="6" pattern="[0-9]{6}" placeholder="000000" required autofocus>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-check me-1"></i>Подтвердить и включить 2FA
                            </button>
                        </form>

                    @else
                        {{-- 2FA не настроена --}}
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                            <div>
                                <strong>2FA не включена</strong><br>
                                Двухфакторная аутентификация повышает безопасность вашего аккаунта.
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.security.2fa.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-shield-plus me-1"></i>Включить 2FA
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
