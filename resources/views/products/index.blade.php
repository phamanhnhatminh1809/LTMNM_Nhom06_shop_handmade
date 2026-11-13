@extends('layouts.app')

@section('title', 'Danh sách sản phẩm')

@push('styles')
<style>
    .shop-layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 850px) {
        .shop-layout { grid-template-columns: 1fr; }
    }
    .filter-box { 
        background: #ffffff; border-radius: 12px; padding: 22px; 
        border: 1px solid #eee7e2; position: sticky; top: 20px;
        box-shadow: 0 2px 8px rgba(121,42,22,0.02);
    }
    .filter-group { margin-bottom: 24px; }
    .filter-group:last-child { margin-bottom: 0; }
    .filter-group h3 {
        font-size: 14px; font-weight: 800; margin: 0 0 12px;
        border-bottom: 1px solid #f2eeea; padding-bottom: 8px;
        text-transform: uppercase; letter-spacing: 0.5px;
        color: var(--color-on-surface, #251914);
        display: flex; align-items: center; gap: 8px;
    }
    .filter-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
    .filter-link {
        display: flex; align-items: center; gap: 8px; text-decoration: none; color: #4b5563;
        padding: 8px 12px; border-radius: 8px; font-size: 13.5px;
        transition: all 0.2s;
    }
    .filter-link:hover { background: #fff5f2; color: var(--color-primary, #792a16); }
    .filter-link.active { background: var(--color-primary, #792a16); color: #fff; font-weight: 700; }
    
    .toolbar {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 22px; padding: 14px 18px; background: #ffffff;
        border-radius: 12px; border: 1px solid #eee7e2; gap: 12px; flex-wrap: wrap;
        box-shadow: 0 2px 8px rgba(121,42,22,0.02);
    }
    .search-bar {
        display: flex; flex: 1; max-width: 420px; position: relative;
    }
    .search-bar input {
        width: 100%; padding: 10px 45px 10px 16px; border: 1px solid #ebdcd7; 
        border-radius: 20px; outline: none; font-size: 14px; background: #faf7f5;
        transition: all 0.2s;
    }
    .search-bar input:focus {
        border-color: var(--color-primary, #792a16);
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(121,42,22,0.1);
    }
    .search-bar button {
        position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; padding: 6px 10px; color: var(--color-primary, #792a16);
        font-size: 16px; display: flex; align-items: center;
    }
</style>
@endpush

@section('content')
@php
    $minP = request('min_price');
    $maxP = request('max_price');
    $minR = request('min_rating');
@endphp

<section class="section" style="padding-top: 24px; padding-bottom: 60px; background: #ffffff; min-height: 80vh;">
    <div class="container">
        <div style="margin-bottom: 24px;">
            <h1 style="font-size: 26px; font-weight: 800; margin-bottom: 6px; color: var(--color-on-surface, #251914);">Danh sách sản phẩm</h1>
            <p style="color: var(--color-on-surface-variant, #55423e); font-size: 14px; margin: 0;">
                @if (!empty($keyword))
                    Kết quả tìm kiếm cho "<strong>{{ $keyword }}</strong>": <strong>{{ $total }}</strong> sản phẩm
                @elseif (!empty($currentCategory))
                    Danh mục "<strong>{{ $currentCategory->name }}</strong>": <strong>{{ $total }}</strong> sản phẩm
                @else
                    Khám phá <strong>{{ $total }}</strong> sản phẩm handmade tinh tế
                @endif
            </p>
        </div>

        <div class="shop-layout">
            <!-- Sidebar bộ lọc -->
            <aside class="filter-box">
                <div class="filter-group">
                    <h3>
                        <i class="bi bi-folder2-open" style="color: var(--color-primary, #792a16);"></i>
                        <span>Danh mục</span>
                    </h3>
                    <ul class="filter-list">
                        <li>
                            <a href="{{ route('products.index', request()->except(['category', 'page'])) }}"
                               class="filter-link {{ empty($categorySlug) ? 'active' : '' }}">
                                <i class="bi bi-grid-fill" style="font-size: 12px;"></i>
                                <span>Tất cả danh mục</span>
                            </a>
                        </li>
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $cat->slug])) }}"
                                   class="filter-link {{ $categorySlug === $cat->slug ? 'active' : '' }}">
                                    <i class="bi {{ $cat->display_icon }}" style="font-size: 13px;"></i>
                                    <span>{{ $cat->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="filter-group">
                    <h3>
                        <i class="bi bi-tag" style="color: var(--color-primary, #792a16);"></i>
                        <span>Khoảng giá</span>
                    </h3>
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
                    <h3>
                        <i class="bi bi-star" style="color: var(--color-primary, #792a16);"></i>
                        <span>Đánh giá</span>
                    </h3>
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
                                <i class="bi bi-star-fill" style="color: #f59e0b; font-size: 13px;"></i>
                                <span>5.0 sao chuẩn</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_rating' => 4])) }}"
                               class="filter-link {{ $minR == 4 ? 'active' : '' }}">
                                <i class="bi bi-star-fill" style="color: #f59e0b; font-size: 13px;"></i>
                                <span>Từ 4.0 sao trở lên</span>
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
                        @foreach (request()->except(['q', 'page']) as $key => $value)
                            @if (!is_array($value) && !empty($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <input type="text" name="q" value="{{ $keyword ?? '' }}" placeholder="Tìm kiếm sản phẩm...">
                        <button type="submit" title="Tìm kiếm">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    <!-- Sắp xếp -->
                    <form method="GET" action="{{ route('products.index') }}" id="sortForm" style="display: flex; align-items: center; gap: 8px;">
                        @foreach (request()->except(['sort', 'page']) as $key => $value)
                            @if (!is_array($value))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <i class="bi bi-arrow-down-up" style="color: var(--color-outline, #89726d);"></i>
                        <select name="sort" onchange="document.getElementById('sortForm').submit()"
                                style="padding: 9px 14px; border-radius: 8px; border: 1px solid #ebdcd7; background: #faf7f5; outline: none; font-size: 13.5px; cursor: pointer; color: var(--color-on-surface);">
                            <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Mặc định</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="bestseller" {{ request('sort') == 'bestseller' ? 'selected' : '' }}>Bán chạy nhất</option>
                        </select>
                    </form>
                </div>

                @if ($products->isEmpty())
                    <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 12px; border: 1px dashed #ebdcd7;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #fff5f2; color: var(--color-primary, #792a16); display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 16px;">
                            <i class="bi bi-inbox"></i>
                        </div>
                        <h3 style="color: var(--color-on-surface, #251914); margin-bottom: 8px; font-size: 17px; font-weight: 700;">Không tìm thấy sản phẩm phù hợp</h3>
                        <p style="color: var(--color-on-surface-variant, #55423e); font-size: 14px; margin: 0 0 16px;">Vui lòng thử lại với từ khóa hoặc điều kiện lọc khác.</p>
                        <a href="{{ route('products.index') }}" class="btn btn--primary btn--sm" style="padding: 8px 18px; border-radius: 8px;">
                            Xóa bộ lọc
                        </a>
                    </div>
                @else
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px;">
                        @foreach ($products as $p)
                            <x-product-card :product="$p" />
                        @endforeach
                    </div>

                    <div style="margin-top: 36px; display: flex; justify-content: center;">
                        {{ $products->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
            </div>
        </div>
    </div>
</section>
@endsection