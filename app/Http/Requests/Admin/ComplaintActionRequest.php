<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintActionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'blocked_reason' => ['nullable', 'string', 'min:3', 'max:1000'],
        ];
    }
}
