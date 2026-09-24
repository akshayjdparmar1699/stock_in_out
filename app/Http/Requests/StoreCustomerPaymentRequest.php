<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $due = $this->route('customer')->dueAmount();

        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.max($due, 0.01)],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        $due = number_format($this->route('customer')->dueAmount(), 2);

        return [
            'amount.max' => "Payment cannot exceed the customer's current due (₹{$due}).",
        ];
    }
}
