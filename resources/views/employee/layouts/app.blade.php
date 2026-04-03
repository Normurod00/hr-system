<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth <meta name="user-id" content="{{ auth()->id() }}"> @endauth

    <title>@yield('title', 'Портал сотрудника') - {{ config('app.name') }}</title>

    <!-- Same fonts as admin -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <!-- Same admin CSS for design system variables & base styles -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" />

    @stack('styles')
</head>

<body class="theme-light">
    <script>
        (function () {
            const saved = localStorage.getItem('hr-theme');
            if (saved === 'dark') {
                document.body.classList.remove('theme-light');
                document.body.classList.add('theme-dark');
            }
        })();
    </script>

    <!-- Toast -->
    @if(session('success') || session('error'))
    @php
        $toastType = session('success') ? 'success' : 'error';
        $toastMessage = session('success') ?? session('error');
        $toastTitle = session('success') ? 'Успешно!' : 'Ошибка!';
        $toastIcon = session('success') ? 'fa-circle-check' : 'fa-circle-xmark';
    @endphp
    <div id="adminToast" class="admin-toast {{ $toastType }}">
        <div class="admin-toast__content">
            <div class="admin-toast__icon"><i class="fa-solid {{ $toastIcon }}"></i></div>
            <div class="admin-toast__message">
                <div class="admin-toast__title">{{ $toastTitle }}</div>
                <div class="admin-toast__text">{{ $toastMessage }}</div>
            </div>
            <button class="admin-toast__close" onclick="closeToast()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="admin-toast__progress"></div>
    </div>
    @endif

    <div id="layoutSidenav">
        <!-- Sidebar -->
        @include('employee.layouts.sidebar')

        <div id="layoutSidenav_content">
            {{-- Top notification bar --}}
            <div style="display:flex; justify-content:flex-end; align-items:center; padding:10px 24px 0; position:relative;">
                @auth
                    @include('components.notification-bell')
                @endauth
            </div>

            <main>
                <!-- Page Header -->
                @hasSection('page-title')
                <div class="page-header">
                    <h1>@yield('page-title')</h1>
                    @hasSection('header-actions')
                    <div class="page-header-actions">@yield('header-actions')</div>
                    @endif
                </div>
                @endif

                @yield('content')
            </main>

            <footer class="admin-footer">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Портал сотрудника.
            </footer>
        </div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fa-solid fa-bars"></i>
    </button>

    <script>
        function closeToast() {
            const toast = document.getElementById('adminToast');
            if (toast) { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 400); }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('adminToast');
            if (toast) setTimeout(() => closeToast(), 3000);

            const body = document.body;
            const railBtn = document.getElementById('sidebarToggleRail');
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const overlay = document.getElementById('sidebarOverlay');
            const keyMini = 'hr-emp-sidebar-mini';

            if (localStorage.getItem(keyMini) === '1') body.classList.add('sidebar-mini');

            function updateChevron() {
                const mini = body.classList.contains('sidebar-mini');
                const icon = railBtn?.querySelector('i');
                if (icon) icon.className = mini ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
            }

            if (railBtn) {
                railBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    body.classList.toggle('sidebar-mini');
                    localStorage.setItem(keyMini, body.classList.contains('sidebar-mini') ? '1' : '0');
                    updateChevron();
                });
            }

            if (mobileBtn) {
                mobileBtn.addEventListener('click', () => {
                    body.classList.toggle('sidebar-open');
                    if (overlay) overlay.toggleAttribute('hidden', !body.classList.contains('sidebar-open'));
                });
            }

            if (overlay) {
                overlay.addEventListener('click', () => {
                    body.classList.remove('sidebar-open');
                    overlay.setAttribute('hidden', '');
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && body.classList.contains('sidebar-open')) {
                    body.classList.remove('sidebar-open');
                    if (overlay) overlay.setAttribute('hidden', '');
                }
            });

            updateChevron();

            // User menu
            const userBtn = document.getElementById('userMenuBtn');
            const userMenu = document.getElementById('userMenu');
            if (userBtn && userMenu) {
                userBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const expanded = userBtn.getAttribute('aria-expanded') === 'true';
                    userBtn.setAttribute('aria-expanded', !expanded);
                    userMenu.hidden = expanded;
                });
                document.addEventListener('click', (e) => {
                    if (!userMenu.hidden && !userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                        userBtn.setAttribute('aria-expanded', 'false');
                        userMenu.hidden = true;
                    }
                });
            }
        });

        function toggleTheme() {
            const body = document.body;
            const isDark = body.classList.contains('theme-dark');
            body.classList.toggle('theme-dark', !isDark);
            body.classList.toggle('theme-light', isDark);
            localStorage.setItem('hr-theme', isDark ? 'light' : 'dark');
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (icon) icon.className = isDark ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
            if (text) text.textContent = isDark ? 'Тёмная тема' : 'Светлая тема';
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
