<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\BranchContext;
use App\Support\InvoiceFormat;
use App\Support\InvoiceFormatData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Settings for the currently selected branch — the admin switches branches
 * with the existing branch switcher to configure each one separately, the
 * same way other branch-scoped admin screens already work.
 *
 * Only Invoice & Templates is built for now; the other sections in the nav
 * are placeholders for later.
 */
class SettingsController extends Controller
{
    public function edit(): View
    {
        $branch = BranchContext::current();

        return view('settings.edit', [
            'branch' => $branch,
            'formats' => InvoiceFormat::all(),
            'selectedFormat' => $branch?->invoice_format,
            'accent' => $branch?->invoice_accent,
        ]);
    }

    public function updateInvoiceFormat(Request $request): RedirectResponse
    {
        $branch = BranchContext::current();
        abort_unless($branch, 404);

        $data = $request->validate([
            'invoice_format' => ['nullable', Rule::in(array_keys(InvoiceFormat::all()))],
            'invoice_accent' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $branch->update([
            'invoice_format' => $data['invoice_format'] ?? null,
            'invoice_accent' => $data['invoice_accent'] ?? null,
        ]);

        return back()->with('status', 'Invoice format updated.');
    }

    /**
     * Previews with the branch's most recent real bill, so what the admin
     * sees matches what a customer would actually receive — no synthetic
     * sample data to keep in sync with every format's own layout.
     */
    public function previewInvoiceFormat(Request $request): Response
    {
        $branch = BranchContext::current();
        abort_unless($branch, 404);

        $invoice = Invoice::where('branch_id', $branch->id)
            ->with(['customer', 'branch.company', 'items.item', 'payments'])
            ->latest('invoice_date')
            ->latest('id')
            ->first();

        if (! $invoice) {
            return response('<p style="font-family: sans-serif; padding: 24px;">No bills on this branch yet — create one first to preview a format.</p>', 200);
        }

        $formatKey = $request->string('format')->toString();
        $view = $formatKey !== '' ? (InvoiceFormat::all()[$formatKey]['view'] ?? null) : null;

        if (! $view) {
            return response()->view('invoices.tax-invoice-pdf', [
                'invoice' => $invoice,
                'customerDue' => $invoice->customer?->dueAmount(),
            ]);
        }

        $data = InvoiceFormatData::build($invoice, forPdf: false);
        $data['accent'] = InvoiceFormat::all()[$formatKey]['accent'] ?? $data['accent'];

        return response()->view($view, $data);
    }
}
