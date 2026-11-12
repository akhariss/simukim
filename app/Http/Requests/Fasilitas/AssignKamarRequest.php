<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class AssignKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'santri_id' => 'required|exists:mahad_santri,id',
            'kamar_id' => 'required|exists:mahad_kamar,id',
            'tanggal_masuk' => 'required|date',
            'catatan' => 'nullable|string|max:500',
        ];
    }
}
