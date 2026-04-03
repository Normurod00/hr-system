<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffChatMessage extends Model
{
    protected $fillable = [
        'staff_chat_id',
        'sender_id',
        'message',
        'attachments',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'read_at' => 'datetime',
        ];
    }

    // ========== Relationships ==========

    public function chat(): BelongsTo
    {
        return $this->belongsTo(StaffChat::class, 'staff_chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ========== Accessors ==========

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    public function getFormattedTimeAttribute(): string
    {
        if (!$this->created_at) return '';

        if ($this->created_at->isToday()) {
            return $this->created_at->format('H:i');
        }
        if ($this->created_at->isYesterday()) {
            return 'Вчера ' . $this->created_at->format('H:i');
        }
        return $this->created_at->format('d.m.Y H:i');
    }
}
