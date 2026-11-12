<?php

namespace App\Enums;

enum LaporanStatus: string
{
    case Draft           = 'draft';
    case MenungguReview  = 'menunggu_review';
    case Direview        = 'direview';
    case Selesai         = 'selesai';

    public function label(): string
    {
        return match($this) {
            self::Draft          => 'Draft',
            self::MenungguReview => 'Menunggu Review',
            self::Direview       => 'Sedang Direview',
            self::Selesai        => 'Selesai',
        };
    }

    public function variant(): string
    {
        return match($this) {
            self::Draft          => 'neutral',
            self::MenungguReview => 'warning',
            self::Direview       => 'info',
            self::Selesai        => 'success',
        };
    }

    public function badgeClass(): string
    {
        return 'badge-' . $this->variant();
    }
}
