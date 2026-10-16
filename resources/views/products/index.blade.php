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
            <!-- Bộ lọc Danh mục -->
            <div class="filter-box">
                <h3>Danh mục</h3>
                <ul class="filter-list">
                    <li>
                        <a href="{{ route('products.index', request()->except(['category', 'page'])) }}" 
                           class="{{ empty($categorySlug) ? 'active' : '' }}">
                            Tất cả danh mục
                        </a>
                    </li>
                    @foreach ($categories as $cat)
                        <li>
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $cat->slug])) }}"
                               class="{{ $categorySlug === $cat->slug ? 'active' : '' }}">
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Bộ lọc Khoảng giá -->
            <div class="filter-box">
                <h3>Khoảng giá</h3>
                <ul class="filter-list">
                    @php
                        $minP = request('min_price');
                        $maxP = request('max_price');
                    @endphp
                    <li>
                        <a href="{{ route('products.index', request()->except(['min_price', 'max_price', 'page'])) }}"
                           class="{{ is_null($minP) && is_null($maxP) ? 'active' : '' }}">
                            Tất cả mức giá
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', array_merge(request()->except(['min_price', 'page']), ['max_price' => 100000])) }}"
                           class="{{ $maxP == 100000 && is_null($minP) ? 'active' : '' }}">
                            Dưới 100.000đ
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_price' => 100000, 'max_price' => 200000])) }}"
                           class="{{ $minP == 100000 && $maxP == 200000 ? 'active' : '' }}">
                            100.000đ - 200.000đ
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_price' => 200000, 'max_price' => 500000])) }}"
                           class="{{ $minP == 200000 && $maxP == 500000 ? 'active' : '' }}">
                            200.000đ - 500.000đ
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', array_merge(request()->except(['max_price', 'page']), ['min_price' => 500000])) }}"
                           class="{{ $minP == 500000 && is_null($maxP) ? 'active' : '' }}">
                            Trên 500.000đ
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Bộ lọc Đánh giá -->
            <div class="filter-box">
                <h3>Đánh giá</h3>
                <ul class="filter-list">
                    @php $minR = request('min_rating'); @endphp
                    <li>
                        <a href="{{ route('products.index', request()->except(['min_rating', 'page'])) }}"
                           class="{{ empty($minR) ? 'active' : '' }}">
                            Tất cả đánh giá
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_rating' => 5])) }}"
                           class="{{ $minR == 5 ? 'active' : '' }}">
                            Từ 5.0 sao
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_rating' => 4])) }}"
                           class="{{ $minR == 4 ? 'active' : '' }}">
                            Từ 4.0 sao trở lên
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', array_merge(request()->except('page'), ['min_rating' => 3])) }}"
                           class="{{ $minR == 3 ? 'active' : '' }}">
                            Từ 3.0 sao trở lên
                        </a>
                    </li>
                </ul>
            </div>

            @if(request()->hasAny(['q', 'category', 'min_price', 'max_price', 'min_rating', 'sort']))
                <div style="margin-top: 14px;">
                    <a href="{{ route('products.index') }}" 
                       style="display: block; text-align: center; font-size: 13px; color: #ef4444; text-decoration: none; padding: 8px 12px; border: 1px dashed #ef4444; border-radius: 6px;">
                        Xóa tất cả bộ lọc
                    </a>
                </div>
            @endif
        </aside>

        <div class="product-main">
            <div class="toolbar">
                <span>Hiển thị <strong>{{ $total }}</strong> sản phẩm</span>
                <form method="GET" action="{{ route('products.index') }}" id="sortForm">
                    @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                    @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
                    @if(request('min_price')) <input type="hidden" name="min_price" value="{{ request('min_price') }}"> @endif
                    @if(request('max_price')) <input type="hidden" name="max_price" value="{{ request('max_price') }}"> @endif
                    @if(request('min_rating')) <input type="hidden" name="min_rating" value="{{ request('min_rating') }}"> @endif

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
                    <h3>Không tìm thấy sản phẩm phù hợp</h3>
                    <p>Hãy thử tìm kiếm với từ khóa khác hoặc điều chỉnh lại các tiêu chí bộ lọc.</p>
                    <a href="{{ route('products.index') }}" class="btn btn--primary" style="margin-top:16px;">Xem tất cả sản phẩm</a>
                </div>
            @else
                <div class="product-grid product-grid--3">
                    @foreach ($products as $p)
                        <x-product-card :product="$p" />
                    @endforeach
                </div>
                
                <div style="margin-top: 32px;">
                    {{ $products->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
