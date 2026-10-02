<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'quantity_unit' => ['nullable', 'in:base,alt'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'alt_unit' => ['nullable', 'string', 'max:20'],
            'alt_unit_ratio' => ['nullable', 'required_with:alt_unit', 'numeric', 'min:0.0001'],
        ];
    }
}
