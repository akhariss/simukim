<?php

namespace App\Http\Requests\Pelaporan;

use Illuminate\Foundation\Http\FormRequest;

class ExportPelaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $inputs = $this->all();
        foreach ($inputs as $key => $val) {
            if ($val === '') {
                $inputs[$key] = null;
            }
        }
        $this->replace($inputs);
    }

    public function rules(): array
    {
        return [
            'tipe'       => ['required', 'string', 'in:semua,master,kehadiran,okupansi,pelanggaran,perizinan,nilai'],
            'format'     => ['required', 'string', 'in:excel,csv,print,pdf'],
            'periode_id' => ['nullable', 'integer', 'exists:mahad_periode,id'],
            'rentang'    => ['nullable', 'string', 'in:periode,bulanan'],
            'bulan'      => ['nullable', 'string', 'max:7', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'kategori'   => ['nullable', 'string', 'in:semua,fasilitas,akademik,kedisiplinan,perizinan'],
            'kelas_id'   => ['nullable', 'integer', 'exists:mahad_kelas,id'],
            'gedung_id'  => ['nullable', 'integer', 'exists:mahad_gedung,id'],
            'status'     => ['nullable', 'string', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'tipe'       => 'Tipe Laporan',
            'format'     => 'Format Ekspor',
            'periode_id' => 'Periode Akademik',
            'rentang'    => 'Rentang Waktu',
            'bulan'      => 'Bulan Laporan',
            'kategori'   => 'Kategori Modul',
            'kelas_id'   => 'Kelas Kajian',
            'gedung_id'  => 'Gedung Asrama',
            'status'     => 'Status',
        ];
    }
}
