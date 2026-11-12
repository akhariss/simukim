<?php

namespace App\Http\Requests\Kelompok;

use Illuminate\Foundation\Http\FormRequest;

class AssignTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'exists:mahad_kelas,id'],
            'tutor_id' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'kelas_id.required' => 'Kajian wajib dipilih.',
            'tutor_id.required' => 'Tutor pengampu wajib dipilih.',
        ];
    }
}
