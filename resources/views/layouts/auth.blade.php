<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tài khoản') - Handmade House</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @stack('styles')
</head>
<body>

    @include('partials.auth-header')

    <main class="auth-main">
        @yield('content')
    </main>

    @include('partials.auth-footer')

    <script src="{{ asset('js/auth.js') }}"></script>
    @stack('scripts')
</body>
</html>
