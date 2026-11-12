<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use HasFactory;

    protected $table = 'mahad_pelanggaran';

    protected $fillable = [
        'periode_id',
        'santri_id',
        'jenis_pelanggaran_id',
        'tanggal',
        'poin',
        'deskripsi',
        'hukuman',
        'status',
        'input_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'poin' => 'integer',
            'status' => \App\Enums\PelanggaranStatus::class,
        ];
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function jenisPelanggaran()
    {
        return $this->belongsTo(PelanggaranJenis::class, 'jenis_pelanggaran_id');
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    public function penindak()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}
