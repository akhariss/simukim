<?php

namespace App\Enums;

enum KelasType: string
{
    case Kelompok = 'kelompok';
    case Masal = 'masal';
    case Lainnya = 'lainnya';

    // Legacy cases compatibility
    case Quran = 'quran';
    case Kitab = 'kitab';

    public function label(): string
    {
        return match($this) {
            self::Kelompok, self::Quran, self::Kitab => 'Kajian Halaqah',
            self::Masal                              => 'Kajian Masal',
            self::Lainnya                            => 'Lainnya',
        };
    }

    public function shortLabel(): string
    {
        return match($this) {
            self::Kelompok, self::Quran, self::Kitab => 'Halaqah',
            self::Masal                              => 'Masal',
            self::Lainnya                            => 'Lainnya',
        };
    }

    public function defaultPertemuan(): int
    {
        return match($this) {
            self::Kelompok, self::Quran, self::Kitab => 2,
            self::Masal                              => 1,
            self::Lainnya                            => 1,
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Kelompok, self::Quran, self::Kitab => 'emerald',
            self::Masal                              => 'amber',
            self::Lainnya                            => 'indigo',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Kelompok, self::Quran, self::Kitab => 'bg-emerald-50 text-emerald-800 border-emerald-200/80 dark:bg-slate-800 dark:text-emerald-300 dark:border-slate-700',
            self::Masal                              => 'bg-amber-50 text-amber-800 border-amber-200/80 dark:bg-slate-800 dark:text-amber-300 dark:border-slate-700',
            self::Lainnya                            => 'bg-indigo-50 text-indigo-800 border-indigo-200/80 dark:bg-slate-800 dark:text-indigo-300 dark:border-slate-700',
        };
    }

    public function iconClass(): string
    {
        return match($this) {
            self::Kelompok, self::Quran, self::Kitab => 'text-emerald-600 dark:text-emerald-400',
            self::Masal                              => 'text-amber-600 dark:text-amber-400',
            self::Lainnya                            => 'text-indigo-600 dark:text-indigo-400',
        };
    }
}
