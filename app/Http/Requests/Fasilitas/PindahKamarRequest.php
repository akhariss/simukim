<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class PindahKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kamar_tujuan_id' => 'required|exists:mahad_kamar,id',
            'tanggal_pindah' => 'required|date',
            'alasan_pindah' => 'nullable|string|max:500',
        ];
    }
}
