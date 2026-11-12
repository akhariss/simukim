<?php

namespace App\Http\Requests\Kelas;

use App\Enums\AbsensiStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'kelompok_id'      => ['required', 'integer', 'exists:mahad_kelompok,id'],
            'kelas_id'         => ['required', 'integer', 'exists:mahad_kelas,id'],
            'jadwal_id'        => ['nullable', 'integer', 'exists:mahad_kelas_jadwal,id'],
            'tanggal'          => ['required', 'date'],
            'catatan_sesi'     => ['nullable', 'string', 'max:500'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.santri_id'=> ['required', 'integer', 'exists:mahad_santri,id'],
            'items.*.status'   => ['required', Rule::enum(AbsensiStatus::class)],
            'items.*.catatan'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kelompok_id'       => 'Kelompok Mahasantri',
            'kelas_id'          => 'Kajian',
            'jadwal_id'         => 'Sesi Jadwal Pertemuan',
            'tanggal'           => 'Tanggal Presensi',
            'items'             => 'Data Presensi',
            'items.*.santri_id' => 'Mahasantri',
            'items.*.status'    => 'Status Kehadiran',
            'items.*.catatan'   => 'Catatan',
        ];
    }
}
