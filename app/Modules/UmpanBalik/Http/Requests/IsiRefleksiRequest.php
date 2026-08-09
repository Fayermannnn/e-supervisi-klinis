<?php

namespace App\Modules\UmpanBalik\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IsiRefleksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'refleksi_guru' => ['required', 'string'],
        ];
    }
}
