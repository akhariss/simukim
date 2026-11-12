<?php

namespace App\Http\Requests\Fasilitas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKamarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor_kamar' => 'required|string|max:20',
            'gender' => 'required|in:L,P',
            'kapasitas_maks' => 'required|integer|min:1|max:10',
            'tipe_ruang' => 'required|in:kamar,mudabbir,wc,tangga,kantor,dapur,kantin,jemuran,koridor',
            'status' => 'required|in:tersedia,penuh,perbaikan,nonaktif',
            'catatan' => 'nullable|string',
        ];
    }
}
