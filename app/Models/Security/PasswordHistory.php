<?php

namespace App\Models\Security;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'password',
        'created_at',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Проверить, использовался ли пароль ранее
     */
    public static function wasUsedBefore(int $userId, string $plainPassword, int $checkLast = 5): bool
    {
        $recent = static::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take($checkLast)
            ->pluck('password');

        foreach ($recent as $hash) {
            if (password_verify($plainPassword, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Сохранить текущий пароль в историю
     */
    public static function savePassword(int $userId, string $hashedPassword): self
    {
        return static::create([
            'user_id' => $userId,
            'password' => $hashedPassword,
            'created_at' => now(),
        ]);
    }
}
