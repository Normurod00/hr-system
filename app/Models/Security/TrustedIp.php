<?php

namespace App\Models\Security;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'label',
        'applies_to',
        'user_id',
        'is_active',
        'created_by',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public static function isAllowed(string $ip, string $scope = 'admin'): bool
    {
        // Если нет записей — всё разрешено (не настроено)
        $hasRules = static::active()->where('applies_to', $scope)->exists();
        if (!$hasRules) {
            return true;
        }

        return static::active()
            ->where('applies_to', $scope)
            ->where('ip_address', $ip)
            ->exists();
    }
}
