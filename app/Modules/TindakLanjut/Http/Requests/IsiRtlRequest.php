<?php

namespace App\Modules\TindakLanjut\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IsiRtlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'deskripsi' => ['required', 'string'],
            'target_waktu' => ['required', 'date'],
            'kategori' => ['nullable', 'string', 'in:pedagogik,kepribadian,sosial,profesional'],
            'status' => ['sometimes', 'string', 'in:belum,sedang,selesai'],
        ];
    }
}
