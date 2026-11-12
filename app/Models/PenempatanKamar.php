<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenempatanKamar extends Model
{
    use HasFactory;

    protected $table = 'mahad_penempatan_kamar';

    protected $fillable = [
        'periode_id',
        'santri_id',
        'kamar_id',
        'tanggal_masuk',
        'tanggal_keluar',
        'status',
        'catatan',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'tanggal_keluar' => 'date',
            'status' => \App\Enums\PenempatanStatus::class,
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

    public function kamar()
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
