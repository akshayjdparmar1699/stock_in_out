<section class="max-w-xl">
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Invoice terms') }}</h2>
        <p class="mt-1 text-sm text-gray-500">{{ __('Shown at the bottom of every invoice PDF. One line per term.') }}</p>
    </header>

    <form method="POST" action="{{ route('profile.invoice-terms') }}" class="mt-5 space-y-3">
        @csrf
        @method('patch')

        <textarea name="invoice_terms" rows="4" placeholder="{{ __("e.g. Goods once sold will not be taken back.\nSubject to Kodinar jurisdiction.") }}"
            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">{{ old('invoice_terms', $invoiceTerms) }}</textarea>
        <p class="text-xs text-gray-400">{{ __("Leave blank to show a default certification line instead.") }}</p>

        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                {{ __('Save') }}
            </button>
            @if (session('status') === 'invoice-terms-updated')
                <p class="text-sm text-green-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
