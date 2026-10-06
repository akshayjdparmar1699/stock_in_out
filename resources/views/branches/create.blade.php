<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Branch') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('branches.store') }}" class="space-y-4">
                    @csrf
                    @include('branches.partials.form', ['branch' => null])

                    <div class="border-t pt-4">
                        <label class="inline-flex items-start gap-2">
                            <input type="checkbox" name="copy_items" value="1" checked
                                class="mt-0.5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">
                                {{ __('Copy all existing items to this branch (same as the main branch)') }}
                                <span class="block text-xs text-gray-400 mt-0.5">
                                    {{ __("Unchecked, this branch starts with no items — they'll only show up here once you purchase or manually stock them for it.") }}
                                </span>
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Save Branch') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
