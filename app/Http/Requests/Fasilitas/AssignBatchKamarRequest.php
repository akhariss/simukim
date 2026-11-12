<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class AssignBatchKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kamar_id' => 'required|integer|exists:mahad_kamar,id',
            'santri_ids' => 'required|array|min:1',
            'santri_ids.*' => 'required|integer|exists:mahad_santri,id',
            'tanggal_masuk' => 'required|date',
            'catatan' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'kamar_id.required' => 'Kamar tujuan wajib dipilih.',
            'kamar_id.exists' => 'Kamar tujuan tidak ditemukan di database.',
            'santri_ids.required' => 'Pilih minimal satu mahasantri untuk ditempatkan.',
            'santri_ids.min' => 'Pilih minimal satu mahasantri untuk ditempatkan.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
        ];
    }
}

