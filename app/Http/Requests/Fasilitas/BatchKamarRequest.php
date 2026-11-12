<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class BatchKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lantai_id' => 'required|exists:mahad_lantai,id',
            'prefix' => 'required|string|max:10',
            'nomor_mulai' => 'required|integer|min:1|max:999',
            'nomor_selesai' => 'required|integer|min:1|max:999|gte:nomor_mulai',
            'padding' => 'required|integer|in:1,2,3',
            'gender' => 'required|in:L,P',
            'kapasitas_maks' => 'required|integer|min:1|max:10',
            'tipe_ruang' => 'required|in:kamar,mudabbir',
        ];
    }
}
