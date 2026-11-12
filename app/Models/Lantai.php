<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lantai extends Model
{
    use HasFactory;

    protected $table = 'mahad_lantai';

    protected $fillable = [
        'gedung_id',
        'nomor_lantai',
        'kapasitas_kamar_standar',
    ];

    public function gedung()
    {
        return $this->belongsTo(Gedung::class, 'gedung_id');
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class, 'lantai_id');
    }
}
