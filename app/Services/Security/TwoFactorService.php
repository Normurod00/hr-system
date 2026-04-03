<?php

namespace App\Services\Security;

use App\Models\Security\UserTwoFactorSetting;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Сгенерировать секрет для пользователя
     */
    public function generateSecret(User $user): UserTwoFactorSetting
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        return UserTwoFactorSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'method' => 'totp',
                'secret' => $secret,
                'recovery_codes' => $recoveryCodes,
                'is_enabled' => false,
                'confirmed_at' => null,
            ]
        );
    }

    /**
     * QR код в формате SVG для Google Authenticator
     */
    public function generateQrCodeSvg(User $user, string $secret): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'HR-BRB'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }

    /**
     * Проверить OTP код
     */
    public function verifyCode(User $user, string $code): bool
    {
        $setting = $user->twoFactorSetting;
        if (!$setting || !$setting->secret) {
            return false;
        }

        $valid = $this->google2fa->verifyKey($setting->secret, $code, 2); // window = 2 (±60 sec)

        if ($valid) {
            $setting->update(['last_used_at' => now()]);
        }

        return $valid;
    }

    /**
     * Подтвердить 2FA (первая активация)
     */
    public function confirm(User $user, string $code): bool
    {
        if (!$this->verifyCode($user, $code)) {
            return false;
        }

        $user->twoFactorSetting->update([
            'is_enabled' => true,
            'confirmed_at' => now(),
        ]);

        return true;
    }

    /**
     * Отключить 2FA
     */
    public function disable(User $user): void
    {
        $setting = $user->twoFactorSetting;
        if ($setting) {
            $setting->update([
                'is_enabled' => false,
                'secret' => null,
                'recovery_codes' => null,
                'confirmed_at' => null,
            ]);
        }
    }

    /**
     * Проверить recovery code
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $setting = $user->twoFactorSetting;
        if (!$setting) {
            return false;
        }

        return $setting->useRecoveryCode($code);
    }

    /**
     * Проверить, включён ли 2FA
     */
    public function isEnabled(User $user): bool
    {
        $setting = $user->twoFactorSetting;
        return $setting && $setting->isConfirmed();
    }

    /**
     * Сгенерировать 8 recovery codes
     */
    protected function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }
}
