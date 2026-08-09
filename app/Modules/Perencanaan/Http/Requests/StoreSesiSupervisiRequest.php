<?php

namespace App\Modules\Perencanaan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSesiSupervisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'guru_id' => ['required', 'uuid', 'exists:pengguna,id'],
            'supervisor_id' => ['required', 'uuid', 'exists:pengguna,id'],
            'tipe_supervisor' => ['required', 'string', 'in:internal,eksternal'],
            'tanggal' => ['required', 'date'],
        ];
    }
}
