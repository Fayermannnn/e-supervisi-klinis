<?php

namespace App\Modules\Pelaporan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * BR-08: "Wajib justifikasi, tercatat audit" - justifikasi divalidasi wajib
 * di sini (basic input validation, bukan business-rule state machine
 * seperti BR-04, sehingga cukup Form Request, tidak perlu ApiException
 * tersendiri).
 */
class DrillDownIndividualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Gate, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'justifikasi' => ['required', 'string', 'min:10'],
        ];
    }
}
