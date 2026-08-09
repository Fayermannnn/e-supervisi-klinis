<?php

namespace App\Modules\PengembanganProfesional\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RekomendasiManualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'materi_id' => ['required', 'uuid', 'exists:materi_pengembangan,id'],
        ];
    }
}
