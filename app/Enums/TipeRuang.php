<?php

namespace App\Enums;

enum TipeRuang: string
{
    case Kamar = 'kamar';
    case Mudabbir = 'mudabbir';
    case Wc = 'wc';
    case Tangga = 'tangga';
    case Kantor = 'kantor';
    case Dapur = 'dapur';
    case Kantin = 'kantin';
    case Jemuran = 'jemuran';
    case Koridor = 'koridor';

    public function label(): string
    {
        return match($this) {
            self::Kamar => 'Kamar Santri',
            self::Mudabbir => 'Kamar Mudabbir',
            self::Wc => 'Toilet / WC',
            self::Tangga => 'Tangga',
            self::Kantor => 'Kantor Pengelola',
            self::Dapur => 'Dapur',
            self::Kantin => 'Kantin',
            self::Jemuran => 'Area Jemuran',
            self::Koridor => 'Koridor',
        };
    }
}
