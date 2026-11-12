<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelanggaranJenis extends Model
{
    use HasFactory;

    protected $table = 'mahad_pelanggaran_jenis';

    protected $fillable = [
        'nama',
        'poin_default',
        'sanksi_default',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'jenis_pelanggaran_id');
    }
}
