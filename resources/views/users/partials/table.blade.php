<div class="bg-white shadow-sm rounded-lg overflow-hidden" :class="mobileView === 'table' ? 'block' : 'hidden lg:block'">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('User') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Role') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Branch') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Can log in') }}</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($users as $user)
                @php $isSelf = $user->id === auth()->id(); @endphp
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-semibold shrink-0">{{ Str::of($user->name)->trim()->substr(0, 2)->upper() }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-gray-900 truncate">
                                    {{ $user->name }}
                                    @if ($isSelf)
                                        <span class="text-xs text-gray-400 font-normal">({{ __('you') }})</span>
                                    @endif
                                </span>
                                <span class="block text-xs text-gray-400 truncate">{{ $user->email }}</span>
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <span @class([
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-brand-100 text-brand-700' => $user->isAdmin(),
                            'bg-blue-100 text-blue-700' => ! $user->isAdmin(),
                        ])>{{ ucfirst($user->role) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->branch->name ?? __('All branches') }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if ($isSelf)
                            <span class="text-gray-500">{{ __('Always (owner)') }}</span>
                        @else
                            <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="button"
                                    data-name="{{ $user->name }}"
                                    data-active="{{ $user->is_active ? '1' : '0' }}"
                                    @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                                    title="{{ $user->is_active ? __('Click to mark inactive') : __('Click to mark active') }}"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $user->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $user->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                            {{ __('Edit') }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-6 text-sm text-gray-400 text-center">{{ __('No users found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

{{-- Mobile cards --}}
<div class="lg:hidden space-y-3" :class="mobileView === 'table' ? 'hidden' : 'block'">
    @forelse ($users as $user)
        @php $isSelf = $user->id === auth()->id(); @endphp
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold shrink-0">{{ Str::of($user->name)->trim()->substr(0, 2)->upper() }}</span>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-gray-900 truncate">
                        {{ $user->name }}
                        @if ($isSelf)
                            <span class="text-xs text-gray-400 font-normal">({{ __('you') }})</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5 truncate">{{ $user->email }}</div>
                </div>
                <span @class([
                    'px-2 py-0.5 rounded-full text-xs font-medium shrink-0',
                    'bg-brand-100 text-brand-700' => $user->isAdmin(),
                    'bg-blue-100 text-blue-700' => ! $user->isAdmin(),
                ])>{{ ucfirst($user->role) }}</span>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 gap-3">
                <span class="text-xs text-gray-500 truncate">{{ $user->branch->name ?? __('All branches') }}</span>
                <div class="flex items-center gap-2 shrink-0">
                    @if ($isSelf)
                        <span class="text-xs text-gray-400">{{ __('Always (owner)') }}</span>
                    @else
                        <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                            @csrf
                            @method('PATCH')
                            <button type="button"
                                data-name="{{ $user->name }}"
                                data-active="{{ $user->is_active ? '1' : '0' }}"
                                @click="confirmForm = $el.closest('form'); confirmName = $el.dataset.name; confirmActive = $el.dataset.active === '1'; confirmOpen = true"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $user->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $user->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('users.edit', $user) }}" class="px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg">{{ __('Edit') }}</a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 py-10 text-sm text-gray-400 text-center">{{ __('No users found.') }}</div>
    @endforelse
</div>

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $users->links() }}
</div>
