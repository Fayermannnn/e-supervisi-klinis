<?php

namespace App\Modules\UmpanBalik\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUmpanBalikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'kekuatan' => ['required', 'string'],
            'area_pengembangan' => ['required', 'string'],
            'rekomendasi' => ['required', 'string'],
            'pendekatan_dipakai' => ['nullable', 'string', 'in:directive_control,directive_informational,collaborative,nondirective'],
        ];
    }
}
