<?php

namespace App\Modules\Pengguna\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenggunaRequest extends FormRequest
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
            'sekolah_id' => ['nullable', 'uuid', 'exists:sekolah,id'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:pengguna,email'],
            'password' => ['required', 'string', 'min:8'],
            'nip_nuptk' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:255'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
