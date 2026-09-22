@php $currentPerPage = \App\Services\PerPagePreference::get(); @endphp

<form method="POST" action="{{ route('preferences.per-page') }}" class="inline-flex items-center gap-2">
    @csrf
    <select id="per_page" name="value" aria-label="{{ __('Rows per page') }}"
        class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach (\App\Services\PerPagePreference::OPTIONS as $option)
            <option value="{{ $option }}" @selected($currentPerPage === $option)>{{ $option }}</option>
        @endforeach
    </select>
</form>
