<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class ImportUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'  => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:4096'],
            'role'  => ['required', 'string', 'in:mahasantri,mudabbir,tutor,admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'  => 'File Excel/CSV wajib diunggah.',
            'file.mimes'     => 'File harus berformat Excel (.xlsx, .xls) atau CSV.',
            'file.max'       => 'Ukuran file maksimal 4 MB.',
            'role.required'  => 'Peran pengguna wajib dipilih.',
            'role.in'        => 'Peran tidak valid.',
        ];
    }
}
