<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#4f46e5">

@php $iconVersion = 2; @endphp
<link rel="icon" type="image/png" sizes="32x32" href="/icons/icon-32.png?v={{ $iconVersion }}">
<link rel="icon" type="image/png" sizes="16x16" href="/icons/icon-16.png?v={{ $iconVersion }}">
<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png?v={{ $iconVersion }}">

<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">

<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
    }
</script>
