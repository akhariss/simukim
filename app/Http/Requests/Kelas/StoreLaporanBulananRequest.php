<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanBulananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('tutor|admin');
    }

    public function rules(): array
    {
        return [
            'kelompok_id' => ['required', 'exists:mahad_kelompok,id'],
            'kelas_id'    => ['required', 'exists:mahad_kelas,id'],
            'bulan'       => ['required', 'integer', 'between:1,12'],
            'tahun'       => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'catatan'     => ['required', 'string', 'min:7'],
            'foto'        => ['required', 'array', 'min:3', 'max:10'],
            'foto.*'      => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'kelompok_id.required' => 'Kelompok binaan wajib dipilih.',
            'kelas_id.required'    => 'Kajian wajib dipilih.',
            'catatan.required'     => 'Catatan laporan kegiatan wajib diisi.',
            'catatan.min'          => 'Catatan laporan minimal 7 karakter.',
            'foto.required'        => 'Wajib mengunggah dokumentasi foto kegiatan.',
            'foto.min'             => 'Wajib melampirkan minimal 3 foto kegiatan per laporan.',
            'foto.max'             => 'Maksimal foto yang dapat diunggah adalah 10 foto.',
            'foto.*.image'         => 'Berkas harus berupa citra foto yang sah.',
            'foto.*.mimes'         => 'Format foto hanya diperbolehkan JPG, JPEG, PNG, atau WEBP.',
            'foto.*.max'           => 'Ukuran masing-masing foto maksimal 2 MB.',
        ];
    }
}
