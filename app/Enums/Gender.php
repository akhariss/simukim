<?php

namespace App\Enums;

enum Gender: string
{
    case L = 'L';
    case P = 'P';

    public function label(): string
    {
        return match($this) {
            self::L => 'Ikhwan (Putra)',
            self::P => 'Akhwat (Putri)',
        };
    }

    public function shortLabel(): string
    {
        return match($this) {
            self::L => 'Ikhwan',
            self::P => 'Akhwat',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::L => 'ikhwan',
            self::P => 'akhwat',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
