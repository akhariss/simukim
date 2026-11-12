<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AssignCampusTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'nama'       => ['nullable', 'string', 'max:255'],
            'no_hp'      => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Nomor induk atau pengenal pengajar wajib diisi.',
            'identifier.string'   => 'Format pengenal pengajar tidak valid.',
        ];
    }
}
