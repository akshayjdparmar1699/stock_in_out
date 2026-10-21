<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['salary', 'upad'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'mode' => ['nullable', Rule::in(['cash', 'upi', 'bank'])],
            'for_month' => ['nullable', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
