<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasJadwal extends Model
{
    use HasFactory;

    protected $table = 'mahad_kelas_jadwal';

    protected $fillable = [
        'kelas_id',
        'nama_sesi',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function getWaktuFormattedAttribute(): string
    {
        $mulai = $this->jam_mulai ? substr($this->jam_mulai, 0, 5) : '00:00';
        $selesai = $this->jam_selesai ? substr($this->jam_selesai, 0, 5) : '00:00';
        return "{$this->hari}, {$mulai} - {$selesai} WIB";
    }
}
