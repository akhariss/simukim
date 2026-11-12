<?php

namespace App\Http\Requests\Kelas;

use Illuminate\Foundation\Http\FormRequest;

class StoreKomponenNilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kelas_id'    => 'required|integer',
            'kelompok_id' => 'nullable|integer',
            'nama'        => 'required|string|max:50',
            'bobot'       => 'required|numeric|min:1|max:100',
        ];
    }
}

