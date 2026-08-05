<?php

namespace App\Modules\Sekolah\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSekolahRequest extends FormRequest
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
            'nama_sekolah' => ['required', 'string', 'max:255'],
            'npsn' => ['nullable', 'string', 'max:20', 'unique:sekolah,npsn'],
            'alamat' => ['nullable', 'string'],
            'status_aktif' => ['sometimes', 'boolean'],
        ];
    }
}
