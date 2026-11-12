<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ClearCacheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:all,data,views,routes,config,prune,key'],
            'key' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Pilih jenis pembersihan cache yang valid.',
            'type.in' => 'Tipe cache yang dipilih tidak didukung sistem.',
        ];
    }
}
