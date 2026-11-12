<?php

namespace App\Enums;

enum PerizinanStatus: string
{
    case Berlaku = 'berlaku';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match($this) {
            self::Berlaku => 'Sedang Berlaku',
            self::Selesai => 'Telah Kembali / Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Berlaku => 'info',
            self::Selesai => 'success',
            self::Dibatalkan => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
