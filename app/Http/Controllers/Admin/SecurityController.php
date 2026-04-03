<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Security\LoginAttempt;
use App\Models\Security\TrustedIp;
use App\Services\Security\PasswordPolicyService;
use App\Services\Security\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function __construct(
        protected TwoFactorService $twoFactor,
        protected PasswordPolicyService $passwordPolicy,
    ) {}

    // ========== 2FA ==========

    /**
     * Страница настройки 2FA
     */
    public function twoFactorSetup(Request $request): View
    {
        $user = $request->user();
        $setting = $user->twoFactorSetting;
        $isEnabled = $this->twoFactor->isEnabled($user);

        $qrCodeSvg = null;
        $secret = null;
        $recoveryCodes = null;

        if ($setting && !$isEnabled) {
            // Сгенерирован, но не подтверждён — показываем QR
            $secret = $setting->secret;
            $qrCodeSvg = $this->twoFactor->generateQrCodeSvg($user, $secret);
            $recoveryCodes = $setting->recovery_codes;
        }

        return view('admin.security.two-factor-setup', compact(
            'isEnabled', 'qrCodeSvg', 'secret', 'recoveryCodes', 'setting'
        ));
    }

    /**
     * Инициировать 2FA — сгенерировать секрет
     */
    public function twoFactorEnable(Request $request): RedirectResponse
    {
        $this->twoFactor->generateSecret($request->user());

        return redirect()->route('admin.security.2fa')
            ->with('success', 'Секретный ключ сгенерирован. Отсканируйте QR-код и введите код для подтверждения.');
    }

    /**
     * Подтвердить 2FA
     */
    public function twoFactorConfirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => 'required|string|digits:6']);

        if ($this->twoFactor->confirm($request->user(), $request->input('code'))) {
            return redirect()->route('admin.security.2fa')
                ->with('success', 'Двухфакторная аутентификация успешно включена.');
        }

        return back()->withErrors(['code' => 'Неверный код. Попробуйте ещё раз.']);
    }

    /**
     * Отключить 2FA
     */
    public function twoFactorDisable(Request $request): RedirectResponse
    {
        $request->validate(['password' => 'required|current_password']);

        $this->twoFactor->disable($request->user());

        $request->session()->forget('2fa_verified');

        return redirect()->route('admin.security.2fa')
            ->with('success', 'Двухфакторная аутентификация отключена.');
    }

    /**
     * Страница ввода OTP при логине
     */
    public function twoFactorChallenge(): View
    {
        return view('admin.security.two-factor-challenge');
    }

    /**
     * Проверка OTP при логине
     */
    public function twoFactorVerify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $user = $request->user();
        $verified = false;

        if ($request->filled('code')) {
            $verified = $this->twoFactor->verifyCode($user, $request->input('code'));
        } elseif ($request->filled('recovery_code')) {
            $verified = $this->twoFactor->verifyRecoveryCode($user, $request->input('recovery_code'));
        }

        if ($verified) {
            $request->session()->put('2fa_verified', true);
            return redirect()->intended('/');
        }

        return back()->withErrors(['code' => 'Неверный код.']);
    }

    // ========== IP Whitelist ==========

    /**
     * Список доверенных IP
     */
    public function trustedIps(): View
    {
        $ips = TrustedIp::with('creator')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.security.trusted-ips', compact('ips'));
    }

    /**
     * Добавить доверенный IP
     */
    public function storeTrustedIp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'label' => 'nullable|string|max:255',
            'applies_to' => 'required|in:admin,all',
            'expires_at' => 'nullable|date|after:now',
        ]);

        TrustedIp::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'IP-адрес добавлен в доверенные.');
    }

    /**
     * Удалить доверенный IP
     */
    public function deleteTrustedIp(TrustedIp $ip): RedirectResponse
    {
        $ip->delete();
        return back()->with('success', 'IP-адрес удалён из доверенных.');
    }

    // ========== Login Attempts ==========

    /**
     * Список попыток входа
     */
    public function loginAttempts(Request $request): View
    {
        $query = LoginAttempt::with('user')
            ->orderByDesc('created_at');

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        if ($request->filled('status')) {
            $query->where('success', $request->input('status') === 'success');
        }

        $attempts = $query->paginate(50);

        return view('admin.security.login-attempts', compact('attempts'));
    }

    // ========== Password Policy ==========

    /**
     * Страница смены пароля (при истечении)
     */
    public function passwordExpired(): View
    {
        return view('admin.security.password-expired');
    }

    /**
     * Смена пароля
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:8',
        ]);

        $newPassword = $request->input('password');
        $user = $request->user();

        // Проверяем политику
        $errors = $this->passwordPolicy->validate($newPassword);
        if (!empty($errors)) {
            return back()->withErrors(['password' => $errors]);
        }

        // Проверяем историю
        if ($this->passwordPolicy->isReused($user, $newPassword)) {
            return back()->withErrors(['password' => 'Этот пароль уже использовался. Выберите другой.']);
        }

        // Обновляем пароль
        $user->update(['password' => Hash::make($newPassword)]);

        // Сохраняем в историю
        $this->passwordPolicy->recordPasswordChange($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Пароль успешно изменён.');
    }
}
