<?php

namespace App\Http\Requests\Kedisiplinan;

use Illuminate\Foundation\Http\FormRequest;

class StorePelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'santri_id' => 'required|exists:mahad_santri,id',
            'jenis_pelanggaran_id' => 'required|exists:mahad_pelanggaran_jenis,id',
            'tanggal' => 'required|date',
            'poin' => 'nullable|integer|min:1|max:100',
            'deskripsi' => 'required|string|max:1000',
            'hukuman' => 'nullable|string|max:255',
            'status' => 'nullable|in:terdaftar,selesai',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Mahasantri wajib dipilih.',
            'santri_id.exists' => 'Mahasantri tidak valid.',
            'jenis_pelanggaran_id.required' => 'Jenis pelanggaran wajib dipilih.',
            'jenis_pelanggaran_id.exists' => 'Jenis pelanggaran tidak valid.',
            'tanggal.required' => 'Tanggal kejadian wajib diisi.',
            'poin.required' => 'Poin pelanggaran wajib diisi.',
            'poin.min' => 'Poin pelanggaran minimal 1.',
            'deskripsi.required' => 'Deskripsi kejadian pelanggaran wajib diisi.',
        ];
    }
}
