<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_PARTNER, User::ROLE_STAFF])],
            'branch_id' => [
                'nullable', 'required_if:role,staff,partner',
                Rule::exists('branches', 'id')->where('company_id', $this->user()?->company_id),
            ],
            // Left blank to keep the current password unchanged.
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'modules' => ['nullable', 'array'],
        ];
    }
}
