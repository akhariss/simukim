<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'nomor_induk' => ['required', 'string', 'max:50', Rule::unique('users', 'nomor_induk')->ignore($userId)],
            'role' => 'required|string|in:admin,tutor,mudabbir,mahasantri',
            'password' => 'nullable|string|min:6',
            'gender' => 'nullable|in:L,P',
            'prodi' => 'nullable|string|max:100',
            'fakultas' => 'nullable|string|max:100',
            'angkatan' => 'nullable|string|max:10',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
            'status' => 'nullable|in:aktif,nonaktif',
        ];
    }
}
