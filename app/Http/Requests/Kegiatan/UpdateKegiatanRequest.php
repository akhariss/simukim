<?php

namespace App\Http\Requests\Kegiatan;

use App\Enums\KegiatanJenis;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'mudabbir']) ?? false;
    }

    public function rules(): array
    {
        return [
            'nama'            => ['required', 'string', 'max:150'],
            'jenis'           => ['required', Rule::enum(KegiatanJenis::class)],
            'hari'            => ['nullable', 'required_if:jenis,rutin', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'jam_mulai'       => ['nullable', 'date_format:H:i'],
            'jam_selesai'     => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi'          => ['nullable', 'string', 'max:150'],
            'deskripsi'       => ['nullable', 'string', 'max:1000'],
            'tanggal_mulai'   => ['nullable', 'required_if:jenis,khusus', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama' => 'Nama Kegiatan',
            'jenis' => 'Jenis Kegiatan',
            'hari' => 'Hari',
            'jam_mulai' => 'Jam Mulai',
            'jam_selesai' => 'Jam Selesai',
            'lokasi' => 'Lokasi Kegiatan',
            'deskripsi' => 'Deskripsi',
            'tanggal_mulai' => 'Tanggal Mulai',
            'tanggal_selesai' => 'Tanggal Selesai',
        ];
    }
}
