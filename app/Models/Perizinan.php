<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perizinan extends Model
{
    use HasFactory;

    protected $table = 'mahad_perizinan';

    protected $fillable = [
        'periode_id',
        'santri_id',
        'jenis_perizinan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'file_bukti',
        'status',
        'input_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'status' => \App\Enums\PerizinanStatus::class,
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

    public function jenisPerizinan()
    {
        return $this->belongsTo(PerizinanJenis::class, 'jenis_perizinan_id');
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }
}
