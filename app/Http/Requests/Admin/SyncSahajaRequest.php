<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SyncSahajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'kode_periode'      => ['nullable', 'string', 'max:20'],
            'status_pembayaran' => ['nullable', 'string', 'in:all,Lunas,Belum Bayar'],
            'gender'            => ['nullable', 'string', 'in:all,L,P'],
        ];
    }
}

