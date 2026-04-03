<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Смена пароля | {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f5f6fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { max-width: 480px; width: 100%; border: none; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="card rounded-4 p-4">
        <div class="card-body">
            <div class="text-center mb-4">
                <i class="bi bi-key" style="font-size: 3rem; color: #f59e0b;"></i>
                <h4 class="mt-2">Смена пароля</h4>
                <p class="text-muted">Ваш пароль истёк. Необходимо установить новый пароль для продолжения работы.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('security.password.update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Текущий пароль</label>
                    <input type="password" name="current_password" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Новый пароль</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                    <div class="form-text">Минимум 8 символов, заглавная и строчная буква, цифра и спецсимвол.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Подтверждение пароля</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-check-lg me-1"></i>Сменить пароль
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center mt-3">
                @csrf
                <button type="submit" class="btn btn-link text-muted">Выйти</button>
            </form>
        </div>
    </div>
</body>
</html>
