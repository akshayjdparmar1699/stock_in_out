<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\User;
use App\Services\PerPagePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->where('company_id', auth()->user()->company_id)
            ->with('branch')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($q) => $q->where('name', 'ilike', $term)->orWhere('email', 'ilike', $term));
            })
            ->orderBy('name')
            ->paginate(PerPagePreference::get())
            ->withQueryString();

        if ($request->ajax()) {
            return view('users.partials.table', ['users' => $users]);
        }

        return view('users.index', ['users' => $users]);
    }

    public function edit(User $user): View
    {
        abort_unless($user->company_id === auth()->user()->company_id, 404);

        $branches = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();

        return view('users.edit', ['user' => $user, 'branches' => $branches]);
    }

    /**
     * Admins can't demote themselves away from admin — role controls
     * whether a login is locked to one branch at all, so doing that to
     * your own account could lock you out of the rest of the app.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_unless($user->company_id === auth()->user()->company_id, 404);

        $data = $request->validated();

        if ($user->id === auth()->id() && $data['role'] !== User::ROLE_ADMIN) {
            throw ValidationException::withMessages([
                'role' => "You can't change your own role away from admin.",
            ]);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'branch_id' => $data['role'] === User::ROLE_STAFF ? $data['branch_id'] : null,
            ...(! empty($data['password']) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        return redirect()->route('users.index')->with('status', "User \"{$user->name}\" updated.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_unless($user->company_id === auth()->user()->company_id, 404);

        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('status', "You can't deactivate your own account.")
                ->with('status_type', 'danger');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'active' : 'inactive';

        return redirect()->back()
            ->with('status', "User \"{$user->name}\" marked {$status}.")
            ->with('status_type', $user->is_active ? 'success' : 'danger');
    }
}
