<?php

namespace App\Enums;

enum DocumentType: string
{
    case Contract = 'contract';
    case Diploma = 'diploma';
    case Certificate = 'certificate';
    case IdDocument = 'id_document';
    case Medical = 'medical';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Contract => 'Трудовой договор',
            self::Diploma => 'Диплом',
            self::Certificate => 'Сертификат',
            self::IdDocument => 'Удостоверение личности',
            self::Medical => 'Медицинская справка',
            self::Other => 'Другой документ',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Contract => 'bi-file-earmark-text',
            self::Diploma => 'bi-mortarboard',
            self::Certificate => 'bi-patch-check',
            self::IdDocument => 'bi-person-badge',
            self::Medical => 'bi-heart-pulse',
            self::Other => 'bi-file-earmark',
        };
    }
}
