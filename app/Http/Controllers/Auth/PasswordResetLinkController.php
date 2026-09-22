<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Hardcoded WhatsApp number that reset links are sent to for now
     * (this app has no per-admin phone number field yet).
     */
    private const WHATSAPP_NUMBER = '919664604781';

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __(Password::INVALID_USER)]);
        }

        $token = Password::broker()->createToken($user);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        $message = "Password reset link for {$user->email}:\n{$resetUrl}";
        $whatsappUrl = 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode($message);

        return back()->with('status', __('Reset link ready — click below to send it on WhatsApp.'))
            ->with('resetWhatsappUrl', $whatsappUrl);
    }
}
