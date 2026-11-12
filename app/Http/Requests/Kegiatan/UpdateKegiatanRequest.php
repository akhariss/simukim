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
            'jam_selesai'     => ['nullable', 'date_format:H:i'],
            'lokasi'          => ['nullable', 'string', 'max:150'],
            'deskripsi'       => ['nullable', 'string', 'max:1000'],
            'tanggal_mulai'   => ['nullable', 'required_if:jenis,khusus', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $jenis = $this->input('jenis');
            $jamMulai = $this->input('jam_mulai');
            $jamSelesai = $this->input('jam_selesai');
            $tglMulai = $this->input('tanggal_mulai');
            $tglSelesai = $this->input('tanggal_selesai');

            if ($jamMulai && $jamSelesai) {
                // Hanya wajib jam_selesai > jam_mulai jika kegiatan pada hari yang sama
                $isSameDay = ($jenis === 'rutin') || empty($tglSelesai) || ($tglMulai === $tglSelesai);
                if ($isSameDay && $jamSelesai <= $jamMulai) {
                    $validator->errors()->add('jam_selesai', 'Untuk kegiatan pada hari yang sama, Jam Selesai harus lebih akhir dari Jam Mulai.');
                }
            }
        });
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
