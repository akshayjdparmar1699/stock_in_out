<section class="max-w-xl">
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('GST invoicing') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Turn this on to print proper GST tax invoices, with HSN codes and CGST/SGST/IGST.') }}</p>
    </header>

    <div class="mt-5 bg-gray-50 rounded-xl p-4 flex items-center justify-between gap-4">
        <div class="min-w-0">
            <div class="font-semibold text-gray-900">{{ __('Enable GST invoicing') }}</div>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ __("Once on, every bill gets a 'Make this a GST invoice' option — it stays off for any bill you don't tick.") }}
            </p>
        </div>
        <form method="POST" action="{{ route('profile.gst-toggle') }}">
            @csrf
            <button type="submit"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0 {{ $gstEnabled ? 'bg-green-500' : 'bg-gray-300' }}">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $gstEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
        </form>
    </div>

    @if ($gstEnabled)
        <p class="mt-3 text-xs text-gray-400">
            {{ __('Add your GSTIN and state on each branch (Branches), and an HSN code and GST rate on each item, so the tax invoice can calculate correctly.') }}
        </p>
    @endif
</section>
