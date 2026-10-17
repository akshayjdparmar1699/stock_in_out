<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * The public marketing page at "/". Anyone already signed in is sent
     * straight to the screen they actually use instead of a sales pitch.
     */
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isSuperAdmin() ? 'companies.index' : 'dashboard');
        }

        return view('home');
    }
}
