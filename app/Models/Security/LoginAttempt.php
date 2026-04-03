<?php

namespace App\Models\Security;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'failure_reason',
        'user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Количество неудачных попыток за N минут
     */
    public static function recentFailedCount(string $email, int $minutes = 15): int
    {
        return static::where('email', $email)
            ->where('success', false)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Заблокирован ли аккаунт (5+ неудачных за 15 мин)
     */
    public static function isLocked(string $email, int $maxAttempts = 5, int $minutes = 15): bool
    {
        return static::recentFailedCount($email, $minutes) >= $maxAttempts;
    }

    /**
     * Записать попытку
     */
    public static function record(
        string $email,
        string $ip,
        bool $success,
        ?string $failureReason = null,
        ?int $userId = null,
        ?string $userAgent = null,
    ): self {
        return static::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'success' => $success,
            'failure_reason' => $failureReason,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }
}
