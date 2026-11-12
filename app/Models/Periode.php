<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    use HasFactory;

    protected $table = 'mahad_periode';

    protected $fillable = [
        'kode',
        'academic_period_id',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Ambil Periode Aktif dengan Caching Otomatis (Anti Repeated DB Hits)
     */
    public static function getActive(): ?self
    {
        return \Illuminate\Support\Facades\Cache::remember('active_periode', 300, function () {
            return static::where('is_active', true)->first()
                ?? static::orderBy('tanggal_mulai', 'desc')->first();
        });
    }

    /**
     * Ambil ID Periode Aktif
     */
    public static function getActiveId(): ?int
    {
        return static::getActive()?->id;
    }

    protected static function booted(): void
    {
        static::saved(fn() => \Illuminate\Support\Facades\Cache::forget('active_periode'));
        static::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('active_periode'));
    }

    public function penempatanKamar()
    {
        return $this->hasMany(PenempatanKamar::class, 'periode_id');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'periode_id');
    }

    public function laporanBulanan()
    {
        return $this->hasMany(LaporanBulanan::class, 'periode_id');
    }

    public function perizinan()
    {
        return $this->hasMany(Perizinan::class, 'periode_id');
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'periode_id');
    }

    public function kegiatan()
    {
        return $this->hasMany(Kegiatan::class, 'periode_id');
    }
}
