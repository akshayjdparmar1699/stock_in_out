<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PerPagePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
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

    public function toggleActive(User $user): RedirectResponse
    {
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
