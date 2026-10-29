<div class="flex items-start gap-3">
    <span class="shrink-0 w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3.6 9h16.8M3.6 15h16.8M12 3a14.5 14.5 0 014 9 14.5 14.5 0 01-4 9 14.5 14.5 0 01-4-9 14.5 14.5 0 014-9z" />
        </svg>
    </span>
    <div class="min-w-0">
        <h3 class="font-semibold text-gray-800">{{ __('Language') }}</h3>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('What language the app is shown in for you — everyone on your account can pick their own.') }}</p>
    </div>
</div>

<form method="POST" action="{{ route('profile.locale') }}" class="mt-5" x-data="{ submitting: false }" @change="submitting = true; $el.submit()">
    @csrf
    @method('PATCH')

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        @foreach (config('app.available_locales') as $code => $label)
            <label @class([
                'relative flex items-center justify-center gap-2 rounded-xl border-2 p-3 cursor-pointer text-sm font-medium',
                'border-brand-600 bg-brand-50/40' => app()->getLocale() === $code,
                'border-gray-200' => app()->getLocale() !== $code,
            ]) :class="submitting ? 'opacity-60 pointer-events-none' : ''">
                <input type="radio" name="locale" value="{{ $code }}" class="sr-only" @checked(app()->getLocale() === $code)>
                <span>{{ $label }}</span>
                @if (app()->getLocale() === $code)
                    <svg class="w-4 h-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                @endif
            </label>
        @endforeach
    </div>
</form>
