<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePenilaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nilai_angka' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_huruf' => ['nullable', 'string', 'max:5'],
            'catatan'     => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nilai_angka.numeric' => 'Nilai angka harus berupa angka.',
            'nilai_angka.min'     => 'Nilai angka minimal 0.',
            'nilai_angka.max'     => 'Nilai angka maksimal 100.',
            'catatan.max'         => 'Catatan maksimal 500 karakter.',
        ];
    }
}
