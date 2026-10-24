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
            'bank_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'gstin' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'max:255'],
            'qr_code_photo' => ['nullable', 'image', 'max:2048'],
            'remove_qr_code' => ['nullable', 'boolean'],
            'copy_items' => ['nullable', 'boolean'],
            'copy_from_branch_id' => [
                'nullable',
                'required_if:copy_items,1',
                Rule::exists('branches', 'id')->where('company_id', $this->user()?->company_id),
            ],
            'copy_suppliers' => ['nullable', 'boolean'],
            'copy_suppliers_from_branch_id' => [
                'nullable',
                'required_if:copy_suppliers,1',
                Rule::exists('branches', 'id')->where('company_id', $this->user()?->company_id),
            ],
        ];
    }
}
