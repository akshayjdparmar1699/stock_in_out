<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('branches', 'code')->where('company_id', $this->user()?->company_id)->ignore($branchId),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'copy_items' => ['nullable', 'boolean'],
            'copy_from_branch_id' => [
                'nullable',
                'required_if:copy_items,1',
                Rule::exists('branches', 'id')->where('company_id', $this->user()?->company_id),
            ],
        ];
    }
}
