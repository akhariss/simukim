<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gedung extends Model
{
    use HasFactory;

    protected $table = 'mahad_gedung';

    protected $fillable = [
        'kode',
        'nama',
        'gender_khusus',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lantai()
    {
        return $this->hasMany(Lantai::class, 'gedung_id');
    }
}
