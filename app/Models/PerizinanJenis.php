<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerizinanJenis extends Model
{
    use HasFactory;

    protected $table = 'mahad_perizinan_jenis';

    protected $fillable = [
        'nama',
        'butuh_bukti',
        'batas_hari_maks',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'butuh_bukti' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function perizinan()
    {
        return $this->hasMany(Perizinan::class, 'jenis_perizinan_id');
    }
}
