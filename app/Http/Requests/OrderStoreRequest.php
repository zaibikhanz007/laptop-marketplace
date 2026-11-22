<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(['cod', 'bank_transfer'])],
        ];
    }
}
