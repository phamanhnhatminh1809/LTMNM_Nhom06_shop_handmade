<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') - Handmade House</title>
    @include('partials.favicon')
    <!-- Tối ưu font tải không chặn render (Render-blocking optimization) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Nunito+Sans:ital,opsz,wght@0,6..12,300;0,6..12,400;0,6..12,600;0,6..12,700;0,6..12,800;1,6..12,400&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    <script type="speculationrules">
        {
            "prefetch": [
                {
                    "where": {
                        "and": [
                            { "href_matches": "/admin/*" },
                            { "not": { "href_matches": "/logout*" } },
                            { "not": { "selector_matches": "[data-no-prefetch], [target='_blank']" } }
                        ]
                    },
                    "eagerness": "moderate"
                }
            ]
        }
    </script>
    <!-- Chống giật co giãn sidebar trước khi trang hiển thị (Anti-FOUC) -->
    <script>
        (function() {
            try {
                if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-is-collapsed');
                }
            } catch(e) {}
        })();
    </script>
    @stack('styles')
</head>
<body>
    <div class="admin-layout">
        @include('admin.partials.admin-sidebar')

        <main class="admin-main-content" id="adminMainContent">
            @yield('content')
        </main>
    </div>

    <div id="adminToastContainer"
         class="shop-toast-container"
         aria-live="polite"
         aria-atomic="true"></div>

    @include('admin.partials.confirm-modal')

    <script src="{{ asset('js/ui-components.js') }}?v={{ filemtime(public_path('js/ui-components.js')) }}"></script>
    <script src="{{ asset('js/admin/sidebar.js') }}?v={{ filemtime(public_path('js/admin/sidebar.js')) }}"></script>
    <div id="adminPageScripts" hidden>
        @stack('scripts')
    </div>
</body>
</html>
