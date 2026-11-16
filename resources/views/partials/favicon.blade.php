@php
    $faviconPath = public_path('assets/images/favicon.svg');
    $faviconUrl = asset('assets/images/favicon.svg') . (file_exists($faviconPath) ? '?v=' . filemtime($faviconPath) : '');
@endphp
<link rel="icon" type="image/svg+xml" href="{{ $faviconUrl }}">
<link rel="shortcut icon" type="image/svg+xml" href="{{ $faviconUrl }}">
