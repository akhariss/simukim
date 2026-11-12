<?php

namespace App\Enums;

enum PenempatanStatus: string
{
    case Aktif = 'aktif';
    case Pindah = 'pindah';
    case Keluar = 'keluar';

    public function label(): string
    {
        return match($this) {
            self::Aktif => 'Aktif Menetap',
            self::Pindah => 'Pindah Kamar',
            self::Keluar => 'Checkout / Keluar',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Aktif => 'success',
            self::Pindah => 'warning',
            self::Keluar => 'neutral',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
