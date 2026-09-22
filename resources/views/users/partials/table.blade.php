<div class="bg-white shadow-sm rounded-lg overflow-hidden">
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Email') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Role') }}</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Branch') }}</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($users as $user)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-800">
                        {{ $user->name }}
                        @if ($user->id === auth()->id())
                            <span class="text-xs text-gray-400">({{ __('you') }})</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span @class([
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-indigo-100 text-indigo-700' => $user->isAdmin(),
                            'bg-gray-100 text-gray-600' => ! $user->isAdmin(),
                        ])>{{ ucfirst($user->role) }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $user->branch->name ?? __('All branches') }}</td>
                    <td class="px-6 py-4 text-center">
                        @if ($user->id === auth()->id())
                            <span class="text-xs text-gray-300" title="{{ __('You cannot deactivate your own account.') }}">—</span>
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

<div class="flex flex-wrap items-center justify-between gap-3 mt-4" @click="onClick($event)" @change="onChange($event)">
    @include('partials.per-page-selector')
    {{ $users->links() }}
</div>
