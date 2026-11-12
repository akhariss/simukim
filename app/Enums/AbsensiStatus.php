<?php

namespace App\Enums;

enum AbsensiStatus: string
{
    case Hadir = 'hadir';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Alfa = 'alfa';
    case Terlambat = 'terlambat';
    case SakitTambahan = 'sakit_tambahan';

    public function label(): string
    {
        return match ($this) {
            self::Hadir => 'Hadir',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Alfa => 'Alfa',
            self::Terlambat => 'Terlambat',
            self::SakitTambahan => 'Sakit (Lanjutan)',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::Hadir => 'success',
            self::Izin => 'info',
            self::Sakit => 'warning',
            self::Alfa => 'danger',
            self::Terlambat => 'warning',
            self::SakitTambahan => 'warning',
        };
    }

    public function badgeColor(): string
    {
        return 'badge-' . $this->variant();
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::Hadir => 'bg-emerald-500',
            self::Izin => 'bg-amber-500',
            self::Sakit => 'bg-rose-500',
            self::Alfa => 'bg-slate-500',
            self::Terlambat => 'bg-orange-500',
            self::SakitTambahan => 'bg-purple-500',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Hadir => '✓',
            self::Izin => 'i',
            self::Sakit => '+',
            self::Alfa => '✕',
            self::Terlambat => '⏱',
            self::SakitTambahan => '✚',
        };
    }
}
