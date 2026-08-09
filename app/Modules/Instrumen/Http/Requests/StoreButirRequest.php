<?php

namespace App\Modules\Instrumen\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreButirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:10'],
            'dimensi' => ['required', 'string', 'max:255'],
            'teks' => ['required', 'string'],
            'definisi_operasional' => ['nullable', 'string'],
            'bobot' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
