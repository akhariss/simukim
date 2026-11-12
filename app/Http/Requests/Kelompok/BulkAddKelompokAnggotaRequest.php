<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class BulkAddKelompokAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'santri_ids'   => ['required', 'array', 'min:1'],
            'santri_ids.*' => ['required', 'integer', 'exists:mahad_santri,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'santri_ids.required' => 'Pilih minimal satu mahasantri untuk didaftarkan.',
            'santri_ids.min'      => 'Pilih minimal satu mahasantri.',
        ];
    }
}
