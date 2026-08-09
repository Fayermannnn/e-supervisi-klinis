<?php

namespace App\Modules\PengembanganProfesional\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'judul' => ['sometimes', 'string', 'max:255'],
            'kategori' => ['sometimes', 'string', 'in:pedagogik,kepribadian,sosial,profesional'],
            'tautan_atau_deskripsi' => ['nullable', 'string'],
        ];
    }
}
