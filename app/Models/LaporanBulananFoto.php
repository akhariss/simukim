<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanBulananFoto extends Model
{
    use HasFactory;

    protected $table = 'mahad_laporan_bulanan_foto';

    protected $fillable = [
        'laporan_id',
        'file_path',
        'file_name',
        'file_size',
        'caption',
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanBulanan::class, 'laporan_id');
    }
}
