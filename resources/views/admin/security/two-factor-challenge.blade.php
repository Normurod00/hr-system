<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка 2FA | {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f6fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { max-width: 440px; width: 100%; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="card rounded-4 p-4">
        <div class="card-body text-center">
            <div class="mb-3">
                <i class="bi bi-shield-lock" style="font-size: 3rem; color: #E52716;"></i>
            </div>
            <h4 class="mb-1">Двухфакторная аутентификация</h4>
            <p class="text-muted mb-4">Введите 6-значный код из Google Authenticator</p>

            @if($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('security.2fa.verify') }}" id="otpForm">
                @csrf
                <div class="mb-3">
                    <input type="text" name="code" class="form-control form-control-lg text-center font-monospace"
                           maxlength="6" pattern="[0-9]{6}" placeholder="000000" autofocus
                           style="letter-spacing: 0.5em; font-size: 1.5rem;">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Подтвердить</button>
            </form>

            <hr>
            <p class="text-muted small mb-2">Нет доступа к приложению?</p>
            <form method="POST" action="{{ route('security.2fa.verify') }}">
                @csrf
                <div class="input-group mb-2">
                    <input type="text" name="recovery_code" class="form-control" placeholder="Код восстановления">
                    <button type="submit" class="btn btn-outline-secondary">Использовать</button>
                </div>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-link text-muted">Выйти</button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
