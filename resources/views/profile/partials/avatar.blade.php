@php
    $size ??= 'w-16 h-16';
    $textSize ??= 'text-xl';
@endphp
<div class="relative shrink-0" x-data="{ uploading: false }">
    <div class="{{ $size }} rounded-2xl bg-white shadow-sm flex items-center justify-center overflow-hidden">
        @if ($user->avatarUrl())
            <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
        @else
            <span class="font-bold {{ $textSize }} text-brand-700">{{ $initials }}</span>
        @endif
    </div>

    <form id="avatar-form" method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="file" name="avatar" id="avatar-input" accept="image/*"
            @change="uploading = true; $el.form.submit()">
    </form>

    <label for="avatar-input"
        class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-[#FFB020] text-white flex items-center justify-center shadow-sm cursor-pointer"
        :class="uploading && 'opacity-50 pointer-events-none'" :aria-busy="uploading">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.823 1.316z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
        </svg>
    </label>
</div>
