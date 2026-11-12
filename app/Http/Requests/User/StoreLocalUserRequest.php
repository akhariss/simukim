<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocalUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'nomor_induk' => 'required|string|max:50|unique:users,nomor_induk',
            'role' => 'required|string|in:admin,tutor,mudabbir,mahasantri',
            'password' => 'required|string|min:6',
            'gender' => 'nullable|in:L,P',
            'prodi' => 'nullable|string|max:100',
            'angkatan' => 'nullable|string|max:10',
            'no_hp' => 'nullable|string|max:20',
        ];
    }
}
