<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * The public "Send us a message" form on the landing page.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'shop_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        Lead::create($data);

        return redirect(route('home').'#contact')
            ->with('status', __("Thanks! We'll connect with you shortly."));
    }

    /**
     * Platform-owner only — every inquiry from the public site lands here.
     */
    public function index(): View
    {
        return view('leads.index', [
            'leads' => Lead::latest()->paginate(20),
        ]);
    }

    public function markContacted(Lead $lead): RedirectResponse
    {
        $lead->update(['contacted' => ! $lead->contacted]);

        return back();
    }
}
