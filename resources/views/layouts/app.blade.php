<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop Handmade') - Đồ thủ công xinh xắn</title>
    @include('partials.favicon')
    <meta name="description" content="@yield('meta_description', 'Shop Handmade - Đồ thủ công len, móc khóa, túi xách handmade dễ thương.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}?v={{ filemtime(public_path('css/tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/base.css') }}?v={{ filemtime(public_path('css/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}?v={{ filemtime(public_path('css/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}?v={{ filemtime(public_path('css/components.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/ai-chatbox.css') }}?v={{ filemtime(public_path('css/ai-chatbox.css')) }}">

    <style>
        html, body { margin: 0; padding: 0; overflow-x: hidden; }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            box-sizing: border-box;
        }

        /* Lưới sản phẩm dùng chung (trang chủ, danh sách, liên quan) */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        .product-grid > :empty { display: none; }
        .shop-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 900px) {
            .shop-layout { grid-template-columns: 1fr; }
        }
    </style>

    @stack('styles')
    <script type="speculationrules">
        {
            "prefetch": [
                {
                    "where": {
                        "and": [
                            { "href_matches": "/*" },
                            { "not": { "href_matches": "/logout*" } },
                            { "not": { "href_matches": "/admin*" } },
                            { "not": { "selector_matches": "[data-no-prefetch], [target='_blank']" } }
                        ]
                    },
                    "eagerness": "moderate"
                }
            ]
        }
    </script>
</head>
<body data-cart-store-url="{{ route('cart.store') }}" data-cart-index-url="{{ route('cart.index') }}">
    @include('partials.header')

    <div id="shopToastContainer"
         class="shop-toast-container"
         aria-live="polite"
         aria-atomic="true"
         @if(session('success')) data-flash-success="{{ session('success') }}" @endif
         @if(session('error')) data-flash-error="{{ session('error') }}" @endif></div>

    <main class="main-content">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.ai-chatbox')

    <script src="{{ asset('js/ui-components.js') }}?v={{ filemtime(public_path('js/ui-components.js')) }}"></script>
    <script src="{{ asset('js/main.js') }}?v={{ filemtime(public_path('js/main.js')) }}"></script>
    <script src="{{ asset('js/ai-chatbox.js') }}?v={{ filemtime(public_path('js/ai-chatbox.js')) }}"></script>
    @stack('scripts')
</body>
</html>