<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class AutoAssignKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tanggal_masuk' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_masuk.date' => 'Format tanggal masuk tidak valid.',
        ];
    }
}
