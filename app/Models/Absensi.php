<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'mahad_absensi';

    protected $fillable = [
        'kelompok_id',
        'kelas_id',
        'jadwal_id',
        'santri_id',
        'tanggal',
        'status',
        'catatan',
        'input_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status'  => \App\Enums\AbsensiStatus::class,
        ];
    }

    public function scopeForTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    public function scopeForKelompok($query, $kelompokId)
    {
        return $query->where('kelompok_id', $kelompokId);
    }

    public function scopeForKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(KelasJadwal::class, 'jadwal_id');
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
