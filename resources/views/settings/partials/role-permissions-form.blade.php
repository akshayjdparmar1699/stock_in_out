@php
    $actions = ['view' => __('View'), 'add' => __('Add'), 'edit' => __('Edit'), 'delete' => __('Delete')];
    // These two modules have no delete feature in the app at all yet, so a
    // "Delete" checkbox there would do nothing — shown as a dash instead of
    // an unexplained, inert box.
    $noDeleteModules = ['suppliers', 'staff'];
@endphp

<div>
    <h3 class="font-semibold text-gray-900">{{ __('Role permission') }}</h3>
    <p class="text-sm text-gray-500 mt-0.5">{{ __('What a Staff login can see and do in each part of the app. Admins always have full access to everything.') }}</p>

    <form method="POST" action="{{ route('settings.role-permissions') }}" class="mt-5">
        @csrf
        @method('PUT')

        {{-- Desktop / tablet: table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="py-2 pr-4">{{ __('Module') }}</th>
                        @foreach ($actions as $label)
                            <th class="py-2 px-3 text-center">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach (\App\Models\RolePermission::MODULES as $key => $label)
                        @php($perm = $rolePermissions->get($key))
                        <tr>
                            <td class="py-3 pr-4 font-medium text-gray-800">{{ $label }}</td>
                            @foreach ($actions as $action => $actionLabel)
                                <td class="py-3 px-3 text-center">
                                    @if ($action === 'delete' && in_array($key, $noDeleteModules, true))
                                        <span class="text-gray-300">&mdash;</span>
                                    @else
                                        <input type="checkbox" name="modules[{{ $key }}][]" value="{{ $action }}"
                                            @checked($perm ? (bool) $perm->{'can_'.$action} : true)
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: one card per module --}}
        <div class="sm:hidden space-y-3">
            @foreach (\App\Models\RolePermission::MODULES as $key => $label)
                @php($perm = $rolePermissions->get($key))
                <div class="border border-gray-200 rounded-xl p-3">
                    <div class="font-medium text-gray-800 mb-2.5">{{ $label }}</div>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach ($actions as $action => $actionLabel)
                            <label class="flex flex-col items-center gap-1.5 text-xs text-gray-600">
                                @if ($action === 'delete' && in_array($key, $noDeleteModules, true))
                                    <span class="text-gray-300">&mdash;</span>
                                    <span class="text-gray-300">{{ $actionLabel }}</span>
                                @else
                                    <input type="checkbox" name="modules[{{ $key }}][]" value="{{ $action }}"
                                        @checked($perm ? (bool) $perm->{'can_'.$action} : true)
                                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    {{ $actionLabel }}
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-4 mt-5">
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700">
                {{ __('Save') }}
            </button>
            @if (session('status') === 'Role permissions updated.')
                <p class="text-sm text-green-600">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</div>
