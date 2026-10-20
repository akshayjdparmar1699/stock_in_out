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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Address') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('We Owe Them') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($suppliers as $supplier)
                @php $due = $dueBySupplierId[$supplier->id] ?? 0; @endphp
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-800">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="text-brand-700 hover:underline font-medium">{{ $supplier->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->phone ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $supplier->address ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right font-medium {{ $due > 0 ? 'text-red-600' : ($due < 0 ? 'text-green-600' : 'text-gray-400') }}">
                        ₹{{ number_format(abs($due), 2) }}{{ $due < 0 ? ' CR' : '' }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="text-brand-600 hover:underline">{{ __('Edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No suppliers found.') }}</td>
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
                <span class="block text-xs text-gray-400 truncate">{{ $supplier->phone ?? '—' }}{{ $supplier->address ? ' · '.$supplier->address : '' }}</span>
            </span>
            <span class="text-right shrink-0">
                <span class="block text-sm font-bold {{ $due > 0 ? 'text-red-600' : ($due < 0 ? 'text-green-600' : 'text-gray-400') }}">
                    &#8377;{{ number_format(abs($due), 0) }}{{ $due < 0 ? ' CR' : '' }}
                </span>
                <span class="block text-[11px] text-gray-400 mt-0.5">{{ __('we owe') }}</span>
            </span>
        </a>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No suppliers found.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $suppliers->links() }}
</div>
