<?php

namespace App\Models\Security;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTwoFactorSetting extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'secret',
        'recovery_codes',
        'is_enabled',
        'confirmed_at',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'recovery_codes' => 'encrypted:array',
            'is_enabled' => 'boolean',
            'confirmed_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->is_enabled && $this->confirmed_at !== null;
    }

    public function useRecoveryCode(string $code): bool
    {
        $codes = $this->recovery_codes ?? [];
        $index = array_search($code, $codes);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $this->update([
            'recovery_codes' => array_values($codes),
            'last_used_at' => now(),
        ]);

        return true;
    }
}
