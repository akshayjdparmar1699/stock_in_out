<?php

namespace App\Http\Requests;

use App\Services\BranchContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Scoped to items this branch already carries, same as the
            // dropdown it's picked from — manually adding stock isn't a way
            // to bring a new-to-this-branch item in, only Purchase is.
            'item_id' => [
                'required',
                Rule::exists('items', 'id')->where('company_id', $this->user()?->company_id),
                Rule::exists('item_stocks', 'item_id')->where('branch_id', BranchContext::id()),
            ],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'quantity_unit' => ['nullable', 'in:base,alt'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
            'alt_unit' => ['nullable', 'string', 'max:20'],
            'alt_unit_ratio' => ['nullable', 'required_with:alt_unit', 'numeric', 'min:0.0001'],
        ];
    }
}
