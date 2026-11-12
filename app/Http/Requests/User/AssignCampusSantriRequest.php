<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AssignCampusSantriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nim' => ['required', 'string'],
            'nama' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'in:L,P'],
            'prodi' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM mahasiswa wajib diisi.',
            'nim.string' => 'Format NIM tidak valid.',
        ];
    }
}
