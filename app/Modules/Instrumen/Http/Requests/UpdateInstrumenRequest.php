<?php

namespace App\Modules\Instrumen\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstrumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'versi' => ['sometimes', 'integer', 'min:1', Rule::unique('instrumen_observasi', 'versi')->ignore($this->route('instrumen_observasi'))],
            'nama' => ['sometimes', 'string', 'max:255'],
            'skor_min' => ['sometimes', 'integer', 'min:1'],
            'skor_maks' => ['sometimes', 'integer', 'gte:skor_min'],
        ];
    }
}
