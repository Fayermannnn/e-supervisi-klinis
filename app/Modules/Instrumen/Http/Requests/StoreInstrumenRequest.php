<?php

namespace App\Modules\Instrumen\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstrumenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'versi' => ['required', 'integer', 'min:1', 'unique:instrumen_observasi,versi'],
            'nama' => ['required', 'string', 'max:255'],
            'skor_min' => ['sometimes', 'integer', 'min:1'],
            'skor_maks' => ['sometimes', 'integer', 'gte:skor_min'],
        ];
    }
}
