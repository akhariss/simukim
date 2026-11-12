<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class PindahKelompokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'target_kelompok_id' => ['required', 'exists:mahad_kelompok,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_kelompok_id.required' => 'Pilih kelompok tujuan perpindahan santri.',
        ];
    }
}
