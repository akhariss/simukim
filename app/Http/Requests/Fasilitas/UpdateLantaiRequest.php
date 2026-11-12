<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLantaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_lantai' => 'required|integer|min:2|max:20',
            'kapasitas_kamar_standar' => 'required|integer|min:1|max:50',
        ];
    }
}
