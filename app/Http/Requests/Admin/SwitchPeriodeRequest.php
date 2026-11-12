<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SwitchPeriodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'periode_id' => ['required', 'integer', 'exists:mahad_periode,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'periode_id.required' => 'Periode semester wajib dipilih.',
            'periode_id.exists'   => 'Periode semester yang dipilih tidak valid di database.',
        ];
    }
}

