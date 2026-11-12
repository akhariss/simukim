<?php

namespace App\Http\Requests\Kedisiplinan;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusPerizinanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:selesai,dibatalkan',
        ];
    }
}
