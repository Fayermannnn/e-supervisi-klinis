<?php

namespace App\Modules\PengembanganProfesional\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UbahStatusRekomendasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:belum,sedang,selesai'],
        ];
    }
}
