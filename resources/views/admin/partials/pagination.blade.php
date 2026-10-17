{{--
    Component Phân trang chuẩn Artisanal Dashboard (FOR_AGENT_FE.md)
    Đồng bộ với CSS trong public/css/admin.css (.product-pagination-footer, .pagination-controls)
--}}
<div class="product-pagination-footer">
    <div class="pagination-info-text">
        @if ($paginator->total() > 0)
            @if ($paginator->firstItem())
                Hiển thị <strong>{{ $paginator->firstItem() }}</strong> – <strong>{{ $paginator->lastItem() }}</strong> trong tổng số <strong>{{ $paginator->total() }}</strong> kết quả
            @else
                Tổng số <strong>{{ $paginator->total() }}</strong> kết quả
            @endif
        @else
            Không có kết quả nào
        @endif
    </div>

    @if ($paginator->hasPages())
        <div class="pagination-controls">
            {{-- Nút Trang trước --}}
            @if ($paginator->onFirstPage())
                <button type="button" class="btn-page-nav" disabled aria-disabled="true" title="Trang trước">
                    <i class="bi bi-chevron-left"></i>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-page-nav" title="Trang trước" style="text-decoration: none;">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            {{-- Danh sách số trang --}}
            @foreach ($elements as $element)
                {{-- Dấu 3 chấm ngăn cách --}}
                @if (is_string($element))
                    <span class="page-dots">{{ $element }}</span>
                @endif

                {{-- Mảng các liên kết số trang --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-num-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="page-num-btn" style="text-decoration: none;">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Nút Trang sau --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-page-nav" title="Trang sau" style="text-decoration: none;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <button type="button" class="btn-page-nav" disabled aria-disabled="true" title="Trang sau">
                    <i class="bi bi-chevron-right"></i>
                </button>
            @endif
        </div>
    @endif
</div>
