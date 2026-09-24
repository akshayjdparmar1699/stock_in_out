<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
        ];
    }
}
