@extends('layouts.app')

@section('title', 'Danh sách sản phẩm')

@push('styles')
<style>
    .shop-layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 850px) {
        .shop-layout { grid-template-columns: 1fr; }
    }
    .filter-box { 
        background: #fff; border-radius: 12px; padding: 20px; 
        border: 1px solid #eee; position: sticky; top: 20px;
    }
    .filter-group { margin-bottom: 24px; }
    .filter-group:last-child { margin-bottom: 0; }
    .filter-group h3 {
        font-size: 15px; font-weight: 700; margin: 0 0 12px;
        border-bottom: 1px solid #eee; padding-bottom: 8px;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .filter-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
    .filter-link {
        display: block; text-decoration: none; color: #444;
        padding: 8px 12px; border-radius: 6px; font-size: 14px;
        transition: all 0.2s;
    }
    .filter-link:hover { background: #fff1ec; color: #c0392b; }
    .filter-link.active { background: #8b3a1f; color: #fff; font-weight: 600; }
    
    .toolbar {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px; padding: 15px 20px; background: #fff;
        border-radius: 12px; border: 1px solid #eee; gap: 12px; flex-wrap: wrap;
    }
    .search-bar {
        display: flex; flex: 1; max-width: 400px; position: relative;
    }
    .search-bar input {
        width: 100%; padding: 10px 40px 10px 15px; border: 1px solid #ddd; 
        border-radius: 8px; outline: none; font-size: 14px;
    }
    .search-bar button {
        position: absolute; right: 5px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; padding: 8px; color: #888;
    }
</style>
@endpush

@section('content')
@php
    $minP = request('min_price');
    $maxP = request('max_price');
    $minR = request('min_rating');
@endphp

<section class="section" style="padding-top: 20px; background: #fafafa; min-height: 80vh;">
    <div class="container">
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 28px; font-weight: 800; margin-bottom: 8px; color: #2c2c2c;">Danh sách sản phẩm</h1>
            <p style="color: #666; font-size: 15px;">
                @if (!empty($keyword))
                    Kết quả cho "<strong>{{ $keyword }}</strong>": {{ $total }} sản phẩm
                @elseif (!empty($currentCategory))
                    Danh mục "<strong>{{ $currentCategory->name }}</strong>": {{ $total }} sản phẩm
                @else
                    Khám phá <strong>{{ $total }}</strong> sản phẩm handmade xinh xắn
                @endif
            </p>
        </div>

        <div class="shop-layout">
            <!-- Sidebar bộ lọc -->
            <aside class="filter-box">
                <div class="filter-group">
                    <h3>Danh mục</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="{{ route('products.index', request()->except(['category', 'page'])) }}"
                               class="filter-link {{ empty($categorySlug) ? 'active' : '' }}">
                                Tất cả danh mục
                            </a>
                        </li>
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $cat->slug])) }}"
                                   class="filter-link {{ $categorySlug === $cat->slug ? 'active' : '' }}">
                                    {{ $cat->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="filter-group">
                    <h3>Khoảng giá</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="{{ route('products.index', request()->except(['min_price', 'max_price', 'page'])) }}"
                               class="filter-link {{ is_null($minP) && is_null($maxP) ? 'active' : '' }}">
                                Tất cả mức giá
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except(['min_price', 'page']), ['max_price' => 100000])) }}"
                               class="filter-link {{ $maxP == 100000 && is_null($minP) ? 'active' : '' }}">
                                Dưới 100.000đ
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_price' => 100000, 'max_price' => 200000])) }}"
                               class="filter-link {{ $minP == 100000 && $maxP == 200000 ? 'active' : '' }}">
                                100.000đ - 200.000đ
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_price' => 200000, 'max_price' => 500000])) }}"
                               class="filter-link {{ $minP == 200000 && $maxP == 500000 ? 'active' : '' }}">
                                200.000đ - 500.000đ
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except(['max_price', 'page']), ['min_price' => 500000])) }}"
                               class="filter-link {{ $minP == 500000 && is_null($maxP) ? 'active' : '' }}">
                                Trên 500.000đ
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="filter-group">
                    <h3>Đánh giá</h3>
                    <ul class="filter-list">
                        <li>
                            <a href="{{ route('products.index', request()->except(['min_rating', 'page'])) }}"
                               class="filter-link {{ empty($minR) ? 'active' : '' }}">
                                Tất cả đánh giá
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_rating' => 5])) }}"
                               class="filter-link {{ $minR == 5 ? 'active' : '' }}">
                                Từ 5.0 sao
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_rating' => 4])) }}"
                               class="filter-link {{ $minR == 4 ? 'active' : '' }}">
                                Từ 4.0 sao trở lên
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- Cột phải: danh sách sản phẩm -->
            <div style="min-width: 0;">
                <div class="toolbar">
                    <!-- Thanh tìm kiếm -->
                    <form method="GET" action="{{ route('products.index') }}" class="search-bar">
                        {{-- Giữ lại các bộ lọc khác khi tìm kiếm --}}
                        @foreach (request()->except(['q', 'page']) as $key => $value)
                            @if (!is_array($value) && !empty($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <input type="text" name="q" value="{{ $keyword ?? '' }}" placeholder="Tìm kiếm sản phẩm...">
                        <button type="submit">🔍</button>
                    </form>

                    <!-- Sắp xếp -->
                    <form method="GET" action="{{ route('products.index') }}" id="sortForm">
                        @foreach (request()->except(['sort', 'page']) as $key => $value)
                            @if (!is_array($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <select name="sort" onchange="document.getElementById('sortForm').submit()"
                                style="padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 14px; cursor: pointer;">
                            <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Mặc định</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="bestseller" {{ request('sort') == 'bestseller' ? 'selected' : '' }}>Bán chạy nhất</option>
                        </select>
                    </form>
                </div>

                @if ($products->isEmpty())
                    <div style="text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; border: 1px dashed #ddd;">
                        <h3 style="color: #666; margin-bottom: 10px;">Không tìm thấy sản phẩm phù hợp</h3>
                        <p style="color: #999; font-size: 14px;">Vui lòng thử lại với từ khóa hoặc bộ lọc khác.</p>
                    </div>
                @else
                    <!-- Hiển thị lưới sản phẩm 3 cột trên desktop, 2 cột trên tablet -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
                        @foreach ($products as $p)
                            <x-product-card :product="$p" />
                        @endforeach
                    </div>

                    <div style="margin-top: 32px; display: flex; justify-content: center;">
                        {{ $products->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection