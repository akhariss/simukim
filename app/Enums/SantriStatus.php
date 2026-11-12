<?php

namespace App\Enums;

enum SantriStatus: string
{
    case Aktif = 'aktif';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match($this) {
            self::Aktif => 'Aktif',
            self::Nonaktif => 'Nonaktif',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Aktif => 'success',
            self::Nonaktif => 'neutral',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
