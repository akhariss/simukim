<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Santri extends Model
{
    use HasFactory;

    protected $table = 'mahad_santri';

    protected $fillable = [
        'user_id',
        'periode_id',
        'nomor_induk',
        'gender',
        'prodi',
        'fakultas',
        'semester',
        'angkatan',
        'no_hp',
        'wa_santri',
        'alamat',
        'nama_wali',
        'no_hp_wali',
        'hp_ayah',
        'email_ayah',
        'hp_ibu',
        'email_ibu',
        'briva_number',
        'bsb_customer_id',
        'status_pembayaran',
        'nominal_tagihan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gender' => \App\Enums\Gender::class,
            'status' => \App\Enums\SantriStatus::class,
            'semester' => 'integer',
            'nominal_tagihan' => 'integer',
        ];
    }

    public function getNimAttribute(): ?string
    {
        return $this->nomor_induk;
    }

    public function getNamaLengkapAttribute(): string
    {
        return $this->user?->name ?? $this->nomor_induk ?? 'Mahasantri';
    }

    public function getKontakDaruratOrtuAttribute(): array
    {
        return [
            'hp_ayah' => $this->hp_ayah,
            'email_ayah' => $this->email_ayah,
            'hp_ibu' => $this->hp_ibu,
            'email_ibu' => $this->email_ibu,
        ];
    }

    public function getFotoUrlAttribute(): ?string
    {
        return null;
    }

    public function isMudabbir(): bool
    {
        return $this->user?->hasRole('mudabbir') ?? false;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function penempatanKamar()
    {
        return $this->hasMany(PenempatanKamar::class, 'santri_id');
    }

    public function penempatanAktif()
    {
        return $this->hasOne(PenempatanKamar::class, 'santri_id')
            ->where('status', 'aktif')
            ->when(Periode::getActiveId(), fn($q, $id) => $q->where('periode_id', $id));
    }

    public function kelompokAnggota()
    {
        return $this->hasMany(KelompokAnggota::class, 'santri_id');
    }

    public function kelompok()
    {
        return $this->hasOneThrough(
            Kelompok::class,
            KelompokAnggota::class,
            'santri_id',
            'id',
            'id',
            'kelompok_id'
        );
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'santri_id');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'santri_id');
    }

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'santri_id');
    }

    public function perizinan()
    {
        return $this->hasMany(Perizinan::class, 'santri_id');
    }
}
