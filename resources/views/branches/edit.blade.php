<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Branch') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('branches.update', $branch) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('branches.partials.form', ['branch' => $branch])

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Update Branch') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
