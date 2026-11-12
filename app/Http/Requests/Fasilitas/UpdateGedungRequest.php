<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGedungRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $gedungId = $this->route('gedung')?->id ?? $this->route('gedung');

        return [
            'kode' => ['required', 'string', 'max:20', Rule::unique('mahad_gedung', 'kode')->ignore($gedungId)],
            'nama' => ['required', 'string', 'max:100'],
            'gender_khusus' => ['required', 'in:L,P,campur'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }
}
