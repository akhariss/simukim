<?php

namespace App\Http\Requests\Kedisiplinan;

use Illuminate\Foundation\Http\FormRequest;

class StorePerizinanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'santri_id' => 'required|exists:mahad_santri,id',
            'jenis_perizinan_id' => 'required|exists:mahad_perizinan_jenis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|string|max:1000',
            'file_bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'santri_id.required' => 'Mahasantri wajib dipilih.',
            'santri_id.exists' => 'Mahasantri tidak valid.',
            'jenis_perizinan_id.required' => 'Jenis perizinan wajib dipilih.',
            'jenis_perizinan_id.exists' => 'Jenis perizinan tidak ditemukan.',
            'tanggal_mulai.required' => 'Tanggal mulai izin wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai izin wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'alasan.required' => 'Alasan izin wajib diisi.',
            'file_bukti.max' => 'Ukuran file bukti maksimal 2MB.',
        ];
    }
}
