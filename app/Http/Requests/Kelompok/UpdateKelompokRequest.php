<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKelompokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama'       => ['required', 'string', 'max:100'],
            'kode'       => ['nullable', 'string', 'max:30'],
            'kapasitas'  => ['required', 'integer', 'min:1', 'max:500'],
            'is_active'  => ['nullable', 'boolean'],
            'deskripsi'  => ['nullable', 'string', 'max:500'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
