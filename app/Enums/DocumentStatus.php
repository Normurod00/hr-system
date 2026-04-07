<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Parsed = 'parsed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает',
            self::Processing => 'Обработка',
            self::Parsed => 'Обработан',
            self::Failed => 'Ошибка',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Processing => 'info',
            self::Parsed => 'success',
            self::Failed => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Pending => 'bi-clock',
            self::Processing => 'bi-arrow-repeat',
            self::Parsed => 'bi-check-circle',
            self::Failed => 'bi-x-circle',
        };
    }
}
