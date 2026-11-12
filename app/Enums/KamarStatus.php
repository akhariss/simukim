<?php

namespace App\Enums;

enum KamarStatus: string
{
    case Tersedia = 'tersedia';
    case Penuh = 'penuh';
    case Perbaikan = 'perbaikan';
    case Nonaktif = 'nonaktif';

    public function label(): string
    {
        return match($this) {
            self::Tersedia => 'Tersedia',
            self::Penuh => 'Penuh',
            self::Perbaikan => 'Perbaikan',
            self::Nonaktif => 'Nonaktif',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Tersedia => 'success',
            self::Penuh => 'danger',
            self::Perbaikan => 'warning',
            self::Nonaktif => 'neutral',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
