<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasKomponenNilai extends Model
{
    use HasFactory;

    protected $table = 'mahad_kelas_komponen_nilai';

    protected $fillable = [
        'kelas_id',
        'kelompok_id',
        'nama',
        'bobot',
        'is_absensi',
        'is_fixed',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'bobot'      => 'decimal:2',
            'is_absensi' => 'boolean',
            'is_fixed'   => 'boolean',
            'urutan'     => 'integer',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    /**
     * Helper default 4 komponen (Absensi, Tugas, UTS, UAS)
     */
    public static function defaultKomponen(int $kelasId, ?int $kelompokId = null): array
    {
        return [
            [
                'kelas_id'    => $kelasId,
                'kelompok_id' => $kelompokId,
                'nama'        => 'Absensi',
                'bobot'       => 25.00,
                'is_absensi'  => true,
                'is_fixed'    => true,
                'urutan'      => 1,
            ],
            [
                'kelas_id'    => $kelasId,
                'kelompok_id' => $kelompokId,
                'nama'        => 'Tugas',
                'bobot'       => 25.00,
                'is_absensi'  => false,
                'is_fixed'    => false,
                'urutan'      => 2,
            ],
            [
                'kelas_id'    => $kelasId,
                'kelompok_id' => $kelompokId,
                'nama'        => 'UTS',
                'bobot'       => 25.00,
                'is_absensi'  => false,
                'is_fixed'    => false,
                'urutan'      => 3,
            ],
            [
                'kelas_id'    => $kelasId,
                'kelompok_id' => $kelompokId,
                'nama'        => 'UAS',
                'bobot'       => 25.00,
                'is_absensi'  => false,
                'is_fixed'    => false,
                'urutan'      => 4,
            ],
        ];
    }
}

