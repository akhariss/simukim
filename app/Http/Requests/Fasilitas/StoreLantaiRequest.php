<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class StoreLantaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gedung_id' => 'required|exists:mahad_gedung,id',
            'nomor_lantai' => 'required|integer|min:2|max:20',
            'jumlah_kamar' => 'nullable|integer|min:0|max:50',
            'gender' => 'nullable|in:L,P',
            'kapasitas_maks' => 'nullable|integer|min:1|max:10',
        ];
    }
}
