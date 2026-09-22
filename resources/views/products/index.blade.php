@extends('layouts.app')

@section('title', 'Danh sách sản phẩm')

@section('content')
<section class="page-header">
    <div class="container">
        <h1>Danh sách sản phẩm</h1>
        <p>
            @if (!empty($keyword))
                Kết quả cho "<strong>{{ $keyword }}</strong>": {{ $total }} sản phẩm
            @elseif (!empty($currentCategory))
                Danh mục "<strong>{{ $currentCategory->name }}</strong>": {{ $total }} sản phẩm
            @else
                Khám phá {{ $total }} sản phẩm handmade xinh xắn
            @endif
        </p>
    </div>
</section>

<section class="section">
    <div class="container product-layout">
        <aside class="sidebar">
            <div class="filter-box">
                <h3>Danh mục</h3>
                <ul class="filter-list">
                    <li>
                        <a href="{{ route('products.index') }}" class="{{ empty($categorySlug) ? 'active' : '' }}">
                            Tất cả
                        </a>
                    </li>
                    @foreach ($categories as $cat)
                        <li>
                            <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                               class="{{ $categorySlug === $cat->slug ? 'active' : '' }}">
                                {{ $cat->icon ?? '🧶' }} {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="filter-box">
                <h3>Khoảng giá</h3>
                <ul class="filter-list">
                    <li><a href="{{ route('products.index', array_merge(request()->query(), ['max_price' => 100000])) }}">Dưới 100.000đ</a></li>
                    <li><a href="{{ route('products.index', array_merge(request()->query(), ['min_price' => 100000, 'max_price' => 200000])) }}">100.000đ - 200.000đ</a></li>
                    <li><a href="{{ route('products.index', array_merge(request()->query(), ['min_price' => 200000, 'max_price' => 500000])) }}">200.000đ - 500.000đ</a></li>
                    <li><a href="{{ route('products.index', array_merge(request()->query(), ['min_price' => 500000])) }}">Trên 500.000đ</a></li>
                </ul>
            </div>
            <div class="filter-box">
                <h3>Đánh giá</h3>
                <ul class="filter-list">
                    <li><a href="#">⭐⭐⭐⭐⭐ (5 sao)</a></li>
                    <li><a href="#">⭐⭐⭐⭐ (4 sao)</a></li>
                    <li><a href="#">⭐⭐⭐ (3 sao)</a></li>
                </ul>
            </div>
        </aside>

        <div class="product-main">
            <div class="toolbar">
                <span>Hiển thị <strong>{{ $total }}</strong> sản phẩm</span>
                <form method="GET" action="{{ route('products.index') }}" id="sortForm">
                    @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                    @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                    <select name="sort" class="sort-select" onchange="document.getElementById('sortForm').submit()">
                        <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Mặc định</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="bestseller" {{ request('sort') == 'bestseller' ? 'selected' : '' }}>Bán chạy</option>
                    </select>
                </form>
            </div>

            @if ($products->isEmpty())
                <div class="empty-state">
                    <div class="empty-state__icon">🔍</div>
                    <h3>Không tìm thấy sản phẩm</h3>
                    <p>Hãy thử từ khóa khác hoặc xem tất cả sản phẩm.</p>
                    <a href="{{ route('products.index') }}" class="btn btn--primary" style="margin-top:16px;">Xem tất cả sản phẩm</a>
                </div>
            @else
                <div class="product-grid product-grid--3">
                    @foreach ($products as $p)
                        <x-product-card :product="$p" />
                    @endforeach
                </div>
                
                <div style="margin-top: 32px;">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
