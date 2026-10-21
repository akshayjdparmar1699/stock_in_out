@php
    $avatarPalette = [
        'bg-orange-100 text-orange-700', 'bg-purple-100 text-purple-700', 'bg-blue-100 text-blue-700',
        'bg-green-100 text-green-700', 'bg-pink-100 text-pink-700', 'bg-amber-100 text-amber-700',
        'bg-indigo-100 text-indigo-700', 'bg-teal-100 text-teal-700',
    ];
@endphp

{{-- Desktop table --}}
<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Role') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Monthly Salary') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Advance Taken') }}</th>
                @if (Auth::user()->isAdmin())
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Branch') }}</th>
                @endif
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Active') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($staff as $member)
                @php $avatarClass = $avatarPalette[$member->id % count($avatarPalette)]; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('staff.show', $member) }}" class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-semibold shrink-0 {{ $avatarClass }}">{{ Str::of($member->name)->trim()->substr(0, 2)->upper() }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-medium text-brand-700 hover:underline truncate">{{ $member->name }}</span>
                                <span class="block text-xs text-gray-400">{{ $member->phone ?: __('Phone not added') }}</span>
                            </span>
                        </a>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $member->isPartner() ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $member->isPartner() ? __('Partner') : __('Staff') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-right text-gray-700">
                        @if ($member->isPartner())
                            {{ __('No salary') }}
                        @else
                            {{ $member->monthly_salary ? '₹'.number_format($member->monthly_salary, 0) : __('Not set') }}
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-right font-medium {{ $member->totalUpad() > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($member->totalUpad(), 0) }}
                    </td>
                    @if (Auth::user()->isAdmin())
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($member->branches as $branch)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $branch->name }}</span>
                                @endforeach
                            </div>
                        </td>
                    @endif
                    <td class="px-6 py-4 text-center">
                        <form method="POST" action="{{ route('staff.toggle-active', $member) }}">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                data-name="{{ $member->name }}"
                                data-active="{{ $member->is_active ? '1' : '0' }}"
                                @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                                title="{{ $member->is_active ? __('Click to mark inactive') : __('Click to mark active') }}"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $member->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $member->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('staff.show', $member) }}" class="px-3 py-1.5 text-sm font-medium text-brand-700 border border-brand-200 rounded-lg hover:bg-brand-50 whitespace-nowrap">
                                {{ $member->isPartner() ? __('Withdrawals') : __('Pay salary') }}
                            </a>
                            <a href="{{ route('staff.edit', $member) }}" title="{{ __('Edit') }}" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center hover:bg-gray-100 shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No staff or partners found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($staff as $member)
        @php $avatarClass = $avatarPalette[$member->id % count($avatarPalette)]; @endphp
        <div onclick="window.location.href='{{ route('staff.show', $member) }}'" class="block bg-white rounded-xl border border-gray-100 shadow-sm p-4 cursor-pointer">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $avatarClass }}">
                    {{ Str::of($member->name)->trim()->substr(0, 2)->upper() }}
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $member->name }}</div>
                    <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                        <span class="px-1.5 py-0.5 rounded-full {{ $member->isPartner() ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">{{ $member->isPartner() ? __('Partner') : __('Staff') }}</span>
                        <span class="truncate">{{ $member->branches->pluck('name')->join(', ') }}</span>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-sm font-bold text-gray-900">
                        @if ($member->isPartner())
                            {{ __('No salary') }}
                        @else
                            {{ $member->monthly_salary ? '₹'.number_format($member->monthly_salary, 0) : __('Not set') }}
                        @endif
                    </div>
                    @if ($member->totalUpad() > 0)
                        <div class="text-xs text-red-600 font-medium mt-0.5">{{ __('Advance') }} ₹{{ number_format($member->totalUpad(), 0) }}</div>
                    @endif
                </div>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100" onclick="event.stopPropagation()">
                <form method="POST" action="{{ route('staff.toggle-active', $member) }}">
                    @csrf
                    @method('PATCH')
                    <button type="button"
                        data-name="{{ $member->name }}"
                        data-active="{{ $member->is_active ? '1' : '0' }}"
                        @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0 {{ $member->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $member->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
                <div class="flex items-center gap-2">
                    <a href="{{ route('staff.show', $member) }}" class="px-3 py-1.5 text-sm font-medium text-brand-700 border border-brand-200 rounded-lg">
                        {{ $member->isPartner() ? __('Withdrawals') : __('Pay salary') }}
                    </a>
                    <a href="{{ route('staff.edit', $member) }}" class="w-8 h-8 rounded-lg text-gray-500 flex items-center justify-center border border-gray-200 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No staff or partners found.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $staff->links() }}
</div>
