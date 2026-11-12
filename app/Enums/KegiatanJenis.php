<?php

namespace App\Enums;

enum KegiatanJenis: string
{
    case Rutin = 'rutin';
    case Khusus = 'khusus';

    public function label(): string
    {
        return match ($this) {
            self::Rutin => 'Rutin (Berulang)',
            self::Khusus => 'Khusus (Insidental)',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::Rutin => 'info',
            self::Khusus => 'tutor',
        };
    }

    public function badgeColor(): string
    {
        return 'badge-' . $this->variant();
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::Rutin => 'bg-blue-500',
            self::Khusus => 'bg-purple-500',
        };
    }
}
