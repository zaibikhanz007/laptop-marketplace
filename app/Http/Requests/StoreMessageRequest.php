<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Please write a message before sending.',
            'body.max' => 'Messages may not be longer than 2000 characters.',
        ];
    }
}
