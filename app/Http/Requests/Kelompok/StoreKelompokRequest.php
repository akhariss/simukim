<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class StoreKelompokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama'      => ['required', 'string', 'max:100'],
            'kode'      => ['nullable', 'string', 'max:30'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:200'],
            'deskripsi' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'      => 'Nama kelompok wajib diisi.',
            'kapasitas.required' => 'Kapasitas maksimal kelompok wajib ditentukan.',
            'kapasitas.min'      => 'Kapasitas minimal 1 santri.',
        ];
    }
}
