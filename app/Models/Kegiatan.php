<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'mahad_kegiatan';

    protected $fillable = [
        'periode_id',
        'nama',
        'jenis',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => \App\Enums\KegiatanJenis::class,
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPeriode($query, int $periodeId)
    {
        return $query->where('periode_id', $periodeId);
    }

    public function scopeRutin($query)
    {
        return $query->where('jenis', 'rutin');
    }

    public function scopeKhusus($query)
    {
        return $query->where('jenis', 'khusus');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
