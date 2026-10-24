<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('branches.index') }}" class="lg:hidden shrink-0 text-gray-500 -ml-1 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </a>
            <div class="flex-1 min-w-0">
                <nav class="hidden lg:flex items-center gap-1.5 text-sm text-gray-400">
                    <a href="{{ route('branches.index') }}" class="hover:text-gray-600">{{ __('Branches') }}</a>
                    <span>/</span>
                    <span class="text-gray-700 font-medium">{{ $branch->name }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight lg:hidden">{{ __('Edit Branch') }}</h2>
            </div>
            <div class="hidden lg:flex items-center gap-2 shrink-0">
                <a href="{{ route('branches.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" form="branch-form" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                    {{ __('Save Branch') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 pb-28 lg:pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('branches.update', $branch) }}" enctype="multipart/form-data" id="branch-form">
                @csrf
                @method('PUT')

                @include('branches.partials.form', ['branch' => $branch])
            </form>
        </div>

        {{-- Mobile sticky action bar --}}
        <div class="lg:hidden fixed inset-x-0 bottom-16 z-30 bg-white border-t border-gray-100 px-4 py-3 transform-gpu" style="padding-bottom: env(safe-area-inset-bottom);">
            <button type="submit" form="branch-form" class="w-full px-4 py-2.5 bg-brand-700 text-white text-sm font-semibold rounded-lg">
                {{ __('Save Branch') }}
            </button>
        </div>
    </div>
</x-app-layout>
