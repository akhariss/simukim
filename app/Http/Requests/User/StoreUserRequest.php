<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'in:mahasantri,mudabbir,tutor,admin'],
            'auth_source' => ['required', 'string', 'in:campus,local'],
            'nomor_induk' => ['required', 'string', 'max:50', 'unique:users,nomor_induk'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'gender' => ['nullable', 'in:L,P'],
            'prodi' => ['nullable', 'string', 'max:150'],
            'angkatan' => ['nullable', 'string', 'max:10'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'password' => ['nullable', 'string', 'min:4'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Peran pengguna wajib dipilih.',
            'nomor_induk.required' => 'Nomor Induk (NIM / NIP) wajib diisi.',
            'nomor_induk.unique' => 'Nomor Induk ini sudah terdaftar di sistem Ma\'had.',
            'name.required' => 'Nama lengkap pengguna wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 4 karakter.',
        ];
    }
}

