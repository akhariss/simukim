<?php

namespace App\Enums;

enum TingkatPelanggaran: string
{
    case Ringan = 'ringan';
    case Sedang = 'sedang';
    case Berat = 'berat';

    public function label(): string
    {
        return match($this) {
            self::Ringan => 'Pelanggaran Ringan',
            self::Sedang => 'Pelanggaran Sedang',
            self::Berat => 'Pelanggaran Berat',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Ringan => 'info',
            self::Sedang => 'warning',
            self::Berat => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
