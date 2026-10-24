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
 * Settings for the currently selected branch/company. Invoice format is
 * branch-scoped (the admin switches branches with the existing branch
 * switcher to configure each one separately); GST, invoice terms, default
 * payment statuses and subscription are company-wide, same as they were on
 * the Profile page these moved here from — only their saving methods still
 * live on ProfileController/BranchController, unchanged.
 *
 * The rest of the nav (Business profile, Payment details, etc.) is still
 * placeholders for later.
 */
class SettingsController extends Controller
{
    public function edit(): View
    {
        $branch = BranchContext::current();
        $user = auth()->user();
        $company = $user->company;

        return view('settings.edit', [
            'user' => $user,
            'branch' => $branch,
            'formats' => InvoiceFormat::all(),
            'selectedFormat' => $branch && array_key_exists((string) $branch->invoice_format, InvoiceFormat::all())
                ? $branch->invoice_format
                : config('invoice_formats.default'),
            'accent' => $branch?->invoice_accent,
            'gstEnabled' => (bool) $company?->gst_enabled,
            'invoiceTerms' => $company?->invoice_terms,
            'defaultPaymentStatus' => $company?->default_invoice_payment_status ?? 'paid',
            'defaultPurchasePaymentStatus' => $company?->default_purchase_payment_status ?? 'paid',
            'subscription' => $company?->subscription?->load('plan'),
        ]);
    }

    public function updateInvoiceFormat(Request $request): RedirectResponse
    {
        $branch = BranchContext::current();
        abort_unless($branch, 404);

        $data = $request->validate([
            'invoice_format' => ['required', Rule::in(array_keys(InvoiceFormat::all()))],
            'invoice_accent' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $branch->update([
            'invoice_format' => $data['invoice_format'],
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
        $format = InvoiceFormat::all()[$formatKey] ?? InvoiceFormat::all()[config('invoice_formats.default')] ?? null;

        if (! $format) {
            return response()->view('invoices.tax-invoice-pdf', [
                'invoice' => $invoice,
                'customerDue' => $invoice->customer?->dueAmount(),
            ]);
        }

        $data = InvoiceFormatData::build($invoice, forPdf: false);
        $data['accent'] = $format['accent'];

        return response()->view($format['view'], $data);
    }
}
