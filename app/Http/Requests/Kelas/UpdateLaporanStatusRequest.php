<?php

namespace App\Http\Requests\Kelas;

use App\Enums\LaporanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLaporanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'        => ['required', Rule::enum(LaporanStatus::class)],
            'catatan_admin' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status laporan wajib dipilih.',
            'status.enum'     => 'Status laporan tidak valid.',
            'catatan_admin.max' => 'Catatan admin tidak boleh lebih dari 1000 karakter.',
        ];
    }
}
