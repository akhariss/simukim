<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class BatchKelompokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'prefix'      => ['required', 'string', 'max:50'],
            'jumlah'      => ['required', 'integer', 'min:1', 'max:100'],
            'nomor_mulai' => ['required', 'integer', 'min:1'],
            'kapasitas'   => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'prefix.required'      => 'Prefix nama kelompok wajib diisi.',
            'jumlah.required'      => 'Jumlah kelompok wajib diisi.',
            'jumlah.min'           => 'Jumlah kelompok minimal 1.',
            'jumlah.max'           => 'Jumlah kelompok maksimal 100 sekaligus.',
            'nomor_mulai.required' => 'Nomor awal penomoran wajib diisi.',
            'kapasitas.required'   => 'Kapasitas per kelompok wajib ditentukan.',
        ];
    }
}
