<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StaffChat extends Model
{
    protected $fillable = [
        'hr_id',
        'employee_id',
        'last_message_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // ========== Relationships ==========

    public function hr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(StaffChatMessage::class);
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(StaffChatMessage::class)->latestOfMany();
    }

    // ========== Scopes ==========

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('hr_id', $userId)
              ->orWhere('employee_id', $userId);
        });
    }

    // ========== Methods ==========

    /**
     * Получить или создать чат между HR и сотрудником
     */
    public static function getOrCreate(int $hrId, int $employeeId): self
    {
        return static::firstOrCreate(
            ['hr_id' => $hrId, 'employee_id' => $employeeId],
            ['is_active' => true]
        );
    }

    /**
     * Количество непрочитанных сообщений для пользователя
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Отметить все сообщения как прочитанные
     */
    public function markAsReadFor(int $userId): void
    {
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Получить собеседника
     */
    public function getOtherUser(int $myId): ?User
    {
        return $this->hr_id === $myId ? $this->employee : $this->hr;
    }
}
