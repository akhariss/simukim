<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelompok extends Model
{
    use HasFactory;

    protected $table = 'mahad_kelompok';

    protected $fillable = [
        'periode_id',
        'nama',
        'kode',
        'kapasitas',
        'is_active',
        'keterangan',
        'deskripsi',
    ];

    public function getDeskripsiAttribute(): ?string
    {
        return $this->attributes['keterangan'] ?? null;
    }

    public function setDeskripsiAttribute(?string $value): void
    {
        $this->attributes['keterangan'] = $value;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'kapasitas' => 'integer',
        ];
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPeriode(Builder $query, int $periodeId): Builder
    {
        return $query->where('periode_id', $periodeId);
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(KelompokAnggota::class, 'kelompok_id');
    }

    public function santri(): BelongsToMany
    {
        return $this->belongsToMany(Santri::class, 'mahad_kelompok_anggota', 'kelompok_id', 'santri_id')
            ->withTimestamps();
    }

    public function pengampu(): HasMany
    {
        return $this->hasMany(KelompokPengampu::class, 'kelompok_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'kelompok_id');
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'kelompok_id');
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(LaporanBulanan::class, 'kelompok_id');
    }

    // ─── Helpers & Accessors ────────────────────────────────────────────────

    public function getJumlahAnggotaAttribute(): int
    {
        return $this->anggota()->count();
    }

    public function getSisaKapasitasAttribute(): int
    {
        return max(0, $this->kapasitas - $this->jumlah_anggota);
    }
}
