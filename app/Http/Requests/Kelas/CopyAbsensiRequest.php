<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class CopyAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'kelompok_id'  => ['required', 'integer', 'exists:mahad_kelompok,id'],
            'kelas_id'     => ['required', 'integer', 'exists:mahad_kelas,id'],
            'jadwal_id'    => ['nullable', 'integer', 'exists:mahad_kelas_jadwal,id'],
            'from_tanggal' => ['required', 'date'],
            'to_tanggal'   => ['required', 'date'],
        ];
    }
}
