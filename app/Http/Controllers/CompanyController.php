<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Platform-owner only (see routes/web.php's super_admin middleware) — this
 * is how a new paying customer gets onboarded: one company, its first
 * branch, and the admin login to hand them, all in one go.
 */
class CompanyController extends Controller
{
    public function index(): View
    {
        $companies = Company::withCount(['branches', 'users'])->latest()->get();

        return view('companies.index', ['companies' => $companies]);
    }

    public function create(): View
    {
        return view('companies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'branch_name' => ['required', 'string', 'max:255'],
            'branch_code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('branches', 'code')],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email'],
            'admin_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $company = Company::create(['name' => $data['company_name']]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => $data['branch_name'],
            'code' => $data['branch_code'],
        ]);

        User::create([
            'company_id' => $company->id,
            'branch_id' => null,
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('companies.index')
            ->with('status', "Company \"{$company->name}\" created with branch \"{$branch->name}\" — share the admin email/password with them to log in.");
    }
}
