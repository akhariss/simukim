<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class StoreGedungRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:20|unique:mahad_gedung,kode',
            'nama' => 'required|string|max:100',
            'gender_khusus' => 'required|in:L,P,campur',
            'deskripsi' => 'nullable|string',
            'jumlah_lantai' => 'nullable|integer|min:1|max:10',
            'gender_blocks' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'kode.required' => 'Kode gedung wajib diisi.',
            'kode.unique' => 'Kode gedung sudah digunakan oleh gedung lain.',
            'nama.required' => 'Nama gedung wajib diisi.',
            'gender_khusus.required' => 'Zona gender gedung wajib ditentukan.',
        ];
    }
}
