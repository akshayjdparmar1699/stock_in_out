<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'subscription' => $user->isSuperAdmin() ? null : $user->company?->subscription?->load('plan'),
            'gstEnabled' => ! $user->isSuperAdmin() && (bool) $user->company?->gst_enabled,
            'invoiceTerms' => $user->isSuperAdmin() ? null : $user->company?->invoice_terms,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * The company-wide master switch for GST invoicing. Only while this is
     * on does the per-invoice "Make this a GST invoice" toggle appear
     * anywhere.
     */
    public function toggleGst(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        $company->update(['gst_enabled' => ! $company->gst_enabled]);

        return back();
    }

    /**
     * Free-text terms & conditions shown at the bottom of every invoice
     * PDF, GST or not. Left blank, the PDF falls back to a generic
     * certification sentence instead of an empty "Terms" section.
     */
    public function updateInvoiceTerms(Request $request): RedirectResponse
    {
        $request->validate([
            'invoice_terms' => ['nullable', 'string', 'max:2000'],
        ]);

        $request->user()->company->update([
            'invoice_terms' => $request->input('invoice_terms') ?: null,
        ]);

        return back()->with('status', 'invoice-terms-updated');
    }

    /**
     * Replace the user's avatar photo.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'avatar-updated');
    }

    /**
     * A plain JSON export of the account's own profile fields, for the
     * "download your data before deleting" prompt.
     */
    public function downloadData(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'branch' => $user->branch?->name,
            'company' => $user->company?->name,
            'account_created_at' => $user->created_at?->toIso8601String(),
        ])->header('Content-Disposition', 'attachment; filename="my-account-data.json"');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
