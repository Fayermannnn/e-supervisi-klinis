<?php

namespace App\Modules\Perencanaan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PraObservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'fokus_observasi' => ['required', 'string'],
            'level_perkembangan_guru' => ['nullable', 'string', 'in:rendah,sedang_rendah,sedang_tinggi,tinggi'],
        ];
    }
}
