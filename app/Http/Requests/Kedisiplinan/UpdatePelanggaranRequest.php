<?php

namespace App\Http\Requests\Kedisiplinan;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'jenis_pelanggaran_id' => 'required|exists:mahad_pelanggaran_jenis,id',
            'deskripsi' => 'required|string|max:1000',
            'hukuman' => 'nullable|string|max:255',
            'status' => 'required|in:terdaftar,selesai',
            'poin' => 'nullable|integer|min:1|max:100',
        ];
    }
}

