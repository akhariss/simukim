<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'mahad_kamar';

    protected $fillable = [
        'lantai_id',
        'nomor_kamar',
        'tipe_ruang',
        'gender',
        'kapasitas_maks',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\KamarStatus::class,
            'gender' => \App\Enums\Gender::class,
            'tipe_ruang' => \App\Enums\TipeRuang::class,
            'kapasitas_maks' => 'integer',
        ];
    }

    public function lantai()
    {
        return $this->belongsTo(Lantai::class, 'lantai_id');
    }

    public function gedung()
    {
        return $this->hasOneThrough(Gedung::class, Lantai::class, 'id', 'id', 'lantai_id', 'gedung_id');
    }

    public function penempatanKamar()
    {
        return $this->hasMany(PenempatanKamar::class, 'kamar_id');
    }

    public function penempatanAktif()
    {
        return $this->hasMany(PenempatanKamar::class, 'kamar_id')
            ->where('status', 'aktif')
            ->when(Periode::getActiveId(), fn($q, $id) => $q->where('periode_id', $id));
    }

    public function jumlahTerisi(): int
    {
        if ($this->relationLoaded('penempatanAktif')) {
            return $this->penempatanAktif->count();
        }
        return $this->penempatanAktif()->count();
    }

    public function kapasitasTersisa(): int
    {
        return max(0, $this->kapasitas_maks - $this->jumlahTerisi());
    }
}
