<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class ResetDatabaseRequest extends FormRequest
{
    /**
     * Pastikan hanya pengguna dengan role Admin yang berhak melakukan aksi ini
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * Aturan validasi input
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'admin_password' => ['required', 'string'],
            'confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Pesan error kustom
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admin_password.required' => 'Kata sandi akun Administrator Anda wajib diisi.',
            'confirmation.required' => 'Teks konfirmasi wajib diketik.',
        ];
    }

    /**
     * Validasi tambahan pasca-input untuk verifikasi password admin & teks konfirmasi
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = $this->user();
                if (!$user || !Hash::check($this->input('admin_password'), $user->password)) {
                    $validator->errors()->add(
                        'admin_password',
                        'Kata sandi Administrator yang Anda masukkan tidak cocok.'
                    );
                }

                if (trim((string) $this->input('confirmation')) !== 'RESET-DATABASE') {
                    $validator->errors()->add(
                        'confirmation',
                        'Teks konfirmasi salah. Anda wajib mengetik teks persis: RESET-DATABASE'
                    );
                }
            },
        ];
    }
}
