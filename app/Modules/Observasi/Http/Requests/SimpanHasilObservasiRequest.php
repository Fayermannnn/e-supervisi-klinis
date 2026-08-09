<?php

namespace App\Modules\Observasi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimpanHasilObservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'butir' => ['required', 'array', 'min:1'],
            'butir.*.butir_id' => ['required', 'uuid', 'exists:butir_observasi,id'],
            'butir.*.skor' => ['required', 'integer', 'min:1', 'max:100'],
            'status_akhir' => ['sometimes', 'boolean'],
        ];
    }
}
