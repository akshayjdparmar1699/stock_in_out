@php
    $actions = ['view' => __('View'), 'add' => __('Add'), 'edit' => __('Edit'), 'delete' => __('Delete')];
    // These two modules have no delete feature in the app at all yet, so a
    // "Delete" checkbox there would do nothing — shown as a dash instead of
    // an unexplained, inert box.
    $noDeleteModules = ['suppliers', 'staff'];
    $userPermissions = $userPermissions ?? collect();
@endphp

<div class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6">
    <h3 class="font-semibold text-gray-900">{{ __('Module permissions') }}</h3>
    <p class="text-sm text-gray-500 mt-0.5">{{ __('What this person can see and do in each part of the app. Leave everything checked to give them full access.') }}</p>

    {{-- Desktop / tablet: table --}}
    <div class="hidden sm:block overflow-x-auto mt-5">
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
                    @php($perm = $userPermissions->get($key))
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
    <div class="sm:hidden space-y-3 mt-5">
        @foreach (\App\Models\RolePermission::MODULES as $key => $label)
            @php($perm = $userPermissions->get($key))
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
</div>
