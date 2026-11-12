<?php

namespace App\Http\Requests\Kelas;

use App\Enums\KelasType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMasterKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama'               => ['required', 'string', 'max:100'],
            'tipe'               => ['required', Rule::enum(KelasType::class)],
            'is_active'          => ['nullable', 'boolean'],
            'deskripsi'          => ['nullable', 'string', 'max:500'],
            'sesi'               => ['nullable', 'array'],
            'sesi.*.id'          => ['nullable', 'integer'],
            'sesi.*.nama_sesi'   => ['required_with:sesi', 'string', 'max:100'],
            'sesi.*.hari'        => ['required_with:sesi', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'sesi.*.jam_mulai'   => ['required_with:sesi', 'string'],
            'sesi.*.jam_selesai' => ['required_with:sesi', 'string'],
            'sesi.*.ruangan'     => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kajian wajib diisi.',
            'tipe.required' => 'Tipe kajian wajib dipilih.',
        ];
    }
}
