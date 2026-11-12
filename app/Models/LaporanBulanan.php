<?php

namespace App\Models;

use App\Enums\LaporanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanBulanan extends Model
{
    use HasFactory;

    protected $table = 'mahad_laporan_bulanan';

    protected $fillable = [
        'periode_id',
        'kelompok_id',
        'kelas_id',
        'tutor_id',
        'bulan',
        'tahun',
        'catatan',
        'status',
        'catatan_admin',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => LaporanStatus::class,
            'reviewed_at' => 'datetime',
            'bulan'       => 'integer',
            'tahun'       => 'integer',
        ];
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeMenungguReview(Builder $query): Builder
    {
        return $query->where('status', LaporanStatus::MenungguReview);
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

    public function kelompok(): BelongsTo
    {
        return $this->belongsTo(Kelompok::class, 'kelompok_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function foto(): HasMany
    {
        return $this->hasMany(LaporanBulananFoto::class, 'laporan_id');
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function getNamaBulanAttribute(): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $bulan[$this->bulan] ?? '-';
    }
}
