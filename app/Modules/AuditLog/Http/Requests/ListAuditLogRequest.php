<?php

namespace App\Modules\AuditLog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization is done in the Controller via Policy, not here
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'aksi' => ['sometimes', 'nullable', 'string', 'max:100'],
            'dari' => ['sometimes', 'nullable', 'date'],
            'sampai' => ['sometimes', 'nullable', 'date', 'after_or_equal:dari'],
            'cursor' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
