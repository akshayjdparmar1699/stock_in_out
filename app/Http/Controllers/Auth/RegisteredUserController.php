<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the "add staff user" view. Restricted to admins (see routes/auth.php).
     */
    public function create(): View
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('auth.register', ['branches' => $branches]);
    }

    /**
     * Handle an admin creating a new staff/admin user, in the same company
     * as the admin doing the creating.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,staff'],
            'branch_id' => [
                'nullable', 'required_if:role,staff',
                Rule::exists('branches', 'id')->where('company_id', $companyId),
            ],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'branch_id' => $data['role'] === 'staff' ? $data['branch_id'] : null,
            'company_id' => $companyId,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('users.index')->with('status', "User \"{$data['name']}\" created.");
    }
}
