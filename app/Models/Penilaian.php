<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'mahad_penilaian';

    protected $fillable = [
        'kelompok_id',
        'kelas_id',
        'santri_id',
        'nilai_komponen',
        'nilai_angka',
        'nilai_huruf',
        'catatan',
        'input_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_komponen' => 'array',
            'nilai_angka'    => 'decimal:2',
        ];
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function inputBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}
