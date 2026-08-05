<?php

namespace App\Modules\Pengguna\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePenggunaRequest extends FormRequest
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
            'sekolah_id' => ['sometimes', 'nullable', 'uuid', 'exists:sekolah,id'],
            'nama' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('pengguna', 'email')->ignore($this->route('pengguna')),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'nip_nuptk' => ['sometimes', 'nullable', 'string', 'max:255'],
            'no_telepon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
