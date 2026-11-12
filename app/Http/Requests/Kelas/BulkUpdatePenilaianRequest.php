<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdatePenilaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nilai'               => ['required', 'array'],
            'nilai.*.santri_id'   => ['required', 'exists:mahad_santri,id'],
            'nilai.*.nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai.*.catatan'     => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nilai.required'               => 'Daftar nilai wajib dikirimkan.',
            'nilai.*.santri_id.required'   => 'ID santri tidak boleh kosong.',
            'nilai.*.santri_id.exists'     => 'Santri tidak ditemukan dalam sistem.',
            'nilai.*.nilai_angka.numeric'  => 'Nilai harus berupa angka.',
            'nilai.*.nilai_angka.min'      => 'Nilai minimal 0.',
            'nilai.*.nilai_angka.max'      => 'Nilai maksimal 100.',
        ];
    }
}
