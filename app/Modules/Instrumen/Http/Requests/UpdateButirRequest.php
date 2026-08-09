<?php

namespace App\Modules\Instrumen\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateButirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'kode' => ['sometimes', 'string', 'max:10'],
            'dimensi' => ['sometimes', 'string', 'max:255'],
            'teks' => ['sometimes', 'string'],
            'definisi_operasional' => ['nullable', 'string'],
            'bobot' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
