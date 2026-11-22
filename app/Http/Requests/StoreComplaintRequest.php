<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Please provide a subject for your report.',
            'description.required' => 'Please describe the issue.',
        ];
    }
}
