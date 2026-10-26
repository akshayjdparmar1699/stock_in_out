@php
    $avatarPalette = [
        'bg-orange-100 text-orange-700', 'bg-purple-100 text-purple-700', 'bg-blue-100 text-blue-700',
        'bg-green-100 text-green-700', 'bg-pink-100 text-pink-700', 'bg-amber-100 text-amber-700',
        'bg-indigo-100 text-indigo-700', 'bg-teal-100 text-teal-700',
    ];
    $initials = fn (string $name) => collect(explode(' ', trim($name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp

<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Supplier') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('City') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('We Owe Them') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($suppliers as $supplier)
                @php
                    $due = $dueBySupplierId[$supplier->id] ?? 0;
                    $avatar = $avatarPalette[$supplier->id % count($avatarPalette)];
                @endphp
                <tr>
                    <td class="px-6 py-4">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-semibold shrink-0 {{ $avatar }}">{{ $initials($supplier->name) }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-brand-700 hover:underline truncate">{{ $supplier->name }}</span>
                                <span class="block text-xs text-gray-400">{{ $supplier->phone ?: '—' }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->city ?: '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right font-medium {{ $due > 0 ? 'text-red-600' : ($due < 0 ? 'text-green-600' : 'text-gray-400') }}">
                        @if ($due > 0)
                            ₹{{ number_format($due, 2) }}
                        @elseif ($due < 0)
                            ₹{{ number_format(abs($due), 2) }} CR
                        @else
                            {{ __('Settled') }}
                        @endif
                    </td>
                    <td class="px-6 py-4" @click.stop>
                        <div class="flex items-center justify-end gap-1.5" x-data="{ menuOpen: false }">
                            @if ($due > 0)
                                <a href="{{ route('suppliers.show', $supplier) }}#record-payment" class="px-3 py-1.5 rounded-lg bg-brand-600 text-white text-xs font-semibold hover:bg-brand-700">{{ __('Pay') }}</a>
                            @else
                                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-xs font-semibold cursor-not-allowed">{{ __('Pay') }}</span>
                            @endif
                            <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 text-xs font-semibold hover:bg-gray-50">{{ __('+ Buy') }}</a>
                            <div class="relative">
                                <button type="button" @click="menuOpen = !menuOpen" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0zm6 2a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                </button>
                                <div x-show="menuOpen" @click.outside="menuOpen = false" style="display: none"
                                    x-transition class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-10">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No suppliers found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($suppliers as $supplier)
        @php
            $due = $dueBySupplierId[$supplier->id] ?? 0;
            $avatar = $avatarPalette[$supplier->id % count($avatarPalette)];
        @endphp
        <a href="{{ route('suppliers.show', $supplier) }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-full flex items-center justify-center text-xs font-semibold shrink-0 {{ $avatar }}">{{ $initials($supplier->name) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block text-sm font-semibold text-gray-900 truncate">{{ $supplier->name }}</span>
                <span class="block text-xs text-gray-400 truncate">{{ $supplier->city ?: '—' }}{{ $supplier->phone ? ' · '.$supplier->phone : '' }}</span>
            </span>
            <span class="text-right shrink-0">
                @if ($due > 0)
                    <span class="block text-sm font-bold text-red-600">&#8377;{{ number_format($due, 0) }}</span>
                    <span class="block text-[11px] text-gray-400 mt-0.5">{{ __('we owe') }}</span>
                @elseif ($due < 0)
                    <span class="block text-sm font-bold text-green-600">&#8377;{{ number_format(abs($due), 0) }} CR</span>
                @else
                    <span class="block text-sm font-bold text-green-600">{{ __('Settled') }}</span>
                @endif
            </span>
        </a>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No suppliers found.') }}</div>
    @endforelse
</div>

{{-- mb-20 on mobile clears the "+" FAB (fixed, bottom-20, 56px tall) so its Next link isn't covered. --}}
<div class="flex flex-wrap items-center justify-between gap-3 mt-4 mb-20 lg:mb-0" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $suppliers->links() }}
</div>
