<?php

namespace App\Http\Controllers;

use App\Services\PerPagePreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function setPerPage(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'value' => ['required', 'integer', 'in:'.implode(',', PerPagePreference::OPTIONS)],
        ]);

        PerPagePreference::set((int) $request->input('value'));

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->back();
    }
}
