<?php

namespace App\Models;

use App\Enums\KelasType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'mahad_kelas';

    protected $fillable = [
        'periode_id',
        'nama',
        'tipe',
        'is_active',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'tipe'      => KelasType::class,
            'is_active' => 'boolean',
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

    public function scopeOfType(Builder $query, string|KelasType $tipe): Builder
    {
        $val = $tipe instanceof KelasType ? $tipe->value : $tipe;
        return $query->where('tipe', $val);
    }

    // ─── Relationships ──────────────────────────────────────────────────────

    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(KelasJadwal::class, 'kelas_id')->orderBy('hari');
    }

    public function pengampu(): HasMany
    {
        return $this->hasMany(KelompokPengampu::class, 'kelas_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'kelas_id');
    }

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'kelas_id');
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(LaporanBulanan::class, 'kelas_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function getJumlahPertemuanAttribute(): int
    {
        return $this->jadwal()->count();
    }
}
