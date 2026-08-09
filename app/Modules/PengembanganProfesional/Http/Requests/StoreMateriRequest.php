<?php

namespace App\Modules\PengembanganProfesional\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'in:pedagogik,kepribadian,sosial,profesional'],
            'tautan_atau_deskripsi' => ['nullable', 'string'],
        ];
    }
}
