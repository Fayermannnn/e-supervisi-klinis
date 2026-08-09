<?php

namespace App\Modules\UmpanBalik\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UbahPendekatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'pendekatan_dipakai' => ['required', 'string', 'in:directive_control,directive_informational,collaborative,nondirective'],
        ];
    }
}
