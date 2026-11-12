<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class AddKelompokAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'santri_id' => ['required_without:nim', 'nullable', 'exists:mahad_santri,id'],
            'nim'       => ['required_without:santri_id', 'nullable', 'string'],
        ];
    }
}
