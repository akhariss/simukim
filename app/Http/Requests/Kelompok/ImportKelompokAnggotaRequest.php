<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class ImportKelompokAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'file_import' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file_import.required' => 'Unggah berkas Excel/CSV hasil tes penempatan kelompok.',
            'file_import.mimes'    => 'Format berkas harus berupa Excel (.xlsx, .xls) atau CSV (.csv).',
            'file_import.max'      => 'Ukuran berkas maksimal 5 MB.',
        ];
    }
}
