@php
    $paths = [
        'format' => '<path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h7M9 17h7"/>',
        'terms' => '<path d="M4 6h16M4 12h16M4 18h10"/>',
        'check' => '<circle cx="12" cy="12" r="9"/><polyline points="8 12 11 15 16 9"/>',
        'truck' => '<rect x="2" y="7" width="13" height="10"/><path d="M15 10h4l3 3v4h-7"/>',
        'percent' => '<path d="M19 5L5 19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
        'star' => '<path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/>',
        'branch' => '<path d="M4 21V5l8-3 8 3v16M9 21v-5h6v5"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'card' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
        'box' => '<path d="M12 2l9 5v10l-9 5-9-5V7z"/>',
        'user' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/>',
        'chat' => '<path d="M21 12a9 9 0 0 1-13.5 7.8L3 21l1.2-4.5A9 9 0 1 1 21 12z"/>',
        'users' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6M19 8v6M16 11h6"/>',
        'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
        'back' => '<polyline points="15 18 9 12 15 6"/>',
        'chevron' => '<polyline points="9 6 15 12 9 18"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>',
        'tick' => '<polyline points="20 6 9 17 4 12"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v.5M12 11v5"/>',
    ];
@endphp
<svg width="{{ $size ?? 18 }}" height="{{ $size ?? 18 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke ?? 2 }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? '' !!}</svg>
