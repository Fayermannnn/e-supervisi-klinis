<?php

namespace App\Modules\Sekolah\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSekolahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_sekolah' => ['sometimes', 'required', 'string', 'max:255'],
            'npsn' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('sekolah', 'npsn')->ignore($this->route('sekolah')),
            ],
            'alamat' => ['sometimes', 'nullable', 'string'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
