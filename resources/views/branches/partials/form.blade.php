@php $branch ??= null; @endphp

<div>
    <x-input-label for="name" :value="__('Branch Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $branch?->name)" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div>
    <x-input-label for="code" :value="__('Branch Code (short, unique)')" />
    <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $branch?->code)" required />
    <x-input-error :messages="$errors->get('code')" class="mt-2" />
</div>

<div>
    <x-input-label for="address" :value="__('Address')" />
    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $branch?->address)" />
    <x-input-error :messages="$errors->get('address')" class="mt-2" />
</div>

<div>
    <x-input-label for="phone" :value="__('Phone')" />
    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $branch?->phone)" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>
