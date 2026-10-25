@php
    $actions = ['view' => __('View'), 'add' => __('Add'), 'edit' => __('Edit'), 'delete' => __('Delete')];
    // These two modules have no delete feature in the app at all yet, so a
    // "Delete" checkbox there would do nothing — shown as a dash instead of
    // an unexplained, inert box.
    $noDeleteModules = ['suppliers', 'staff'];
    $userPermissions = $userPermissions ?? collect();

    // The desktop table and the mobile card list below both show every
    // module — only one is visible at a time (a CSS media query, not a
    // conditional render) — so a plain checkbox in each would mean two
    // real inputs per module/action sharing one name, and unchecking the
    // one you can see wouldn't touch its hidden twin. Both bind to this
    // single Alpine state instead, keyed "module_action", with one hidden
    // input per checked value as the only thing actually submitted.
    $initial = [];
    foreach (\App\Models\RolePermission::MODULES as $key => $label) {
        $perm = $userPermissions->get($key);
        foreach (array_keys($actions) as $action) {
            if ($action === 'delete' && in_array($key, $noDeleteModules, true)) {
                continue;
            }
            $initial["{$key}_{$action}"] = $perm ? (bool) $perm->{'can_'.$action} : true;
        }
    }
@endphp

<div class="bg-white rounded-2xl border shadow-sm p-5 sm:p-6" x-data="{ perm: @js($initial) }">
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
                    <tr>
                        <td class="py-3 pr-4 font-medium text-gray-800">{{ $label }}</td>
                        @foreach ($actions as $action => $actionLabel)
                            <td class="py-3 px-3 text-center">
                                @if ($action === 'delete' && in_array($key, $noDeleteModules, true))
                                    <span class="text-gray-300">&mdash;</span>
                                @else
                                    <input type="checkbox" x-model="perm['{{ $key }}_{{ $action }}']"
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
            <div class="border border-gray-200 rounded-xl p-3">
                <div class="font-medium text-gray-800 mb-2.5">{{ $label }}</div>
                <div class="grid grid-cols-4 gap-2">
                    @foreach ($actions as $action => $actionLabel)
                        <label class="flex flex-col items-center gap-1.5 text-xs text-gray-600">
                            @if ($action === 'delete' && in_array($key, $noDeleteModules, true))
                                <span class="text-gray-300">&mdash;</span>
                                <span class="text-gray-300">{{ $actionLabel }}</span>
                            @else
                                <input type="checkbox" x-model="perm['{{ $key }}_{{ $action }}']"
                                    class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                {{ $actionLabel }}
                            @endif
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- The only thing actually submitted — one hidden input per checked value. --}}
    @foreach (\App\Models\RolePermission::MODULES as $key => $label)
        @foreach (array_keys($actions) as $action)
            @continue($action === 'delete' && in_array($key, $noDeleteModules, true))
            <template x-if="perm['{{ $key }}_{{ $action }}']">
                <input type="hidden" name="modules[{{ $key }}][]" value="{{ $action }}">
            </template>
        @endforeach
    @endforeach
</div>
