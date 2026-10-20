<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Phone') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Monthly Salary') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total Advance (all time)') }}</th>
                @if (Auth::user()->isAdmin())
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Branches') }}</th>
                @endif
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($staff as $member)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $member->name }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $member->isPartner() ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $member->isPartner() ? __('Partner') : __('Staff') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $member->phone ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right text-gray-700">
                        {{ $member->monthly_salary ? '₹'.number_format($member->monthly_salary, 2) : '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-right font-medium {{ $member->totalUpad() > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($member->totalUpad(), 2) }}
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
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('staff.edit', $member) }}" class="text-brand-600 hover:underline">{{ __('Edit') }}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No staff or partners found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($staff as $member)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 rounded-full flex items-center justify-center text-sm font-semibold shrink-0 {{ $member->isPartner() ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ Str::of($member->name)->trim()->substr(0, 2)->upper() }}
                </span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $member->name }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        <span class="px-1.5 py-0.5 rounded-full {{ $member->isPartner() ? 'bg-brand-100 text-brand-700' : 'bg-gray-100 text-gray-600' }}">{{ $member->isPartner() ? __('Partner') : __('Staff') }}</span>
                        &middot; {{ $member->phone ?? '—' }}
                    </div>
                </div>
                <form method="POST" action="{{ route('staff.toggle-active', $member) }}">
                    @csrf
                    @method('PATCH')
                    <button type="button"
                        data-name="{{ $member->name }}"
                        data-active="{{ $member->is_active ? '1' : '0' }}"
                        @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                        title="{{ $member->is_active ? __('Click to mark inactive') : __('Click to mark active') }}"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0 {{ $member->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $member->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </form>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                <div class="text-xs text-gray-500">
                    {{ __('Salary') }}: <span class="font-medium text-gray-700">{{ $member->monthly_salary ? '₹'.number_format($member->monthly_salary, 2) : '—' }}</span>
                    @if ($member->totalUpad() > 0)
                        <span class="ml-2 text-red-600 font-medium">{{ __('Advance') }} ₹{{ number_format($member->totalUpad(), 2) }}</span>
                    @endif
                </div>
                <a href="{{ route('staff.edit', $member) }}" class="text-sm text-brand-600 hover:underline shrink-0">{{ __('Edit') }}</a>
            </div>
            @if (Auth::user()->isAdmin() && $member->branches->isNotEmpty())
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach ($member->branches as $branch)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">{{ $branch->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No staff or partners found.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $staff->links() }}
</div>
