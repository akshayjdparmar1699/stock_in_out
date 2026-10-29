<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    private const SESSION_PENDING_KEY = 'google_pending';

    /**
     * A `?plan=<key>&years=<n>` query string (carried here from the
     * get-started details page) survives the round trip to Google in the
     * session, so the callback knows which plan a brand-new signup is for.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $request->session()->put('google_intent', [
            'plan' => $request->query('plan') ?: null,
            'years' => \in_array($request->integer('years'), Plan::YEARS_OPTIONS, true) ? $request->integer('years') : 1,
        ]);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('status', __('Google sign-in did not complete. Please try again.'));
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            if (! $user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user, true);
            $request->session()->forget(['google_intent', self::SESSION_PENDING_KEY]);

            return redirect()->intended(route('dashboard'))
                ->with('status', __('Welcome back!'));
        }

        // No existing account — this is a brand-new signup. Business/plan
        // details still need to be collected, so stash the Google identity
        // and continue the normal get-started flow from its details step,
        // just without a password to type.
        $intent = $request->session()->pull('google_intent', []);

        $plan = Plan::where('is_active', true)->where('key', $intent['plan'] ?? null)->first()
            ?? Plan::where('is_active', true)->where('key', Plan::KEY_FREE_TRIAL)->first();

        $request->session()->put(self::SESSION_PENDING_KEY, [
            'google_id' => $googleUser->getId(),
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: __('Google user'),
            'email' => $googleUser->getEmail(),
        ]);

        return redirect()->route('get-started.details', [
            'plan' => $plan->id,
            'years' => $intent['years'] ?? 1,
        ]);
    }

    /**
     * "Not you?" escape hatch on the details page — drops the stashed
     * Google identity so the form falls back to the normal manual fields.
     */
    public function forget(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_PENDING_KEY);

        return redirect()->back();
    }
}
