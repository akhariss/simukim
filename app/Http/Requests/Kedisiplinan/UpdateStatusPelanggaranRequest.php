<?php

namespace App\Http\Requests\Kedisiplinan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPelanggaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:terdaftar,selesai',
            'hukuman' => 'nullable|string|max:255',
        ];
    }
}
