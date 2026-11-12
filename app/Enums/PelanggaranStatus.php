<?php

namespace App\Enums;

enum PelanggaranStatus: string
{
    case Terdaftar = 'terdaftar';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match($this) {
            self::Terdaftar => 'Belum Dilaksanakan',
            self::Selesai => 'Selesai Dilaksanakan',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Terdaftar => 'warning',
            self::Selesai => 'success',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
