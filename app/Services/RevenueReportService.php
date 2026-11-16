<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Tổng hợp số liệu doanh thu và xuất báo cáo Excel (.xlsx) cho khu vực quản trị.
 */
class RevenueReportService
{
    public function __construct(
        private readonly XlsxExportService $xlsxExportService
    ) {
    }

    /**
     * Tạo file .xlsx báo cáo doanh thu theo bộ lọc thời gian và trả về đường dẫn + tên file tải xuống.
     *
     * @param  array{preset?: ?string, start_date?: ?string, end_date?: ?string}  $filters
     * @return array{path: string, filename: string}
     */
    public function exportRevenueXlsx(array $filters, string $exportedBy): array
    {
        $range = $this->resolveDateRange(
            $filters['preset'] ?? null,
            $filters['start_date'] ?? null,
            $filters['end_date'] ?? null
        );

        $ordersQuery = Order::query()->orderByDesc('created_at')->orderByDesc('id');
        if ($range['start'] && $range['end']) {
            $ordersQuery->whereBetween('created_at', [$range['start'], $range['end']]);
        }
        $orders = $ordersQuery->get();

        $dailyRows = $this->buildDailyRevenueRows($orders);
        $categoryRows = $this->buildCategoryRevenueRows($range['start'], $range['end']);
        $productRows = $this->buildProductRevenueRows($range['start'], $range['end']);

        $sheets = [
            $this->buildSummarySheet($orders, $dailyRows, $categoryRows, $range['label'], $exportedBy),
            $this->buildProductSheet($productRows, $range['label']),
            $this->buildOrdersSheet($orders, $range['label']),
        ];

        $xlsxPath = $this->xlsxExportService->buildToTempFile($sheets);

        return [
            'path'     => $xlsxPath,
            'filename' => 'bao-cao-doanh-thu-' . now()->format('Ymd-His') . '.xlsx',
        ];
    }

    /**
     * Xác định khoảng thời gian lọc từ preset hoặc ngày tùy chỉnh.
     *
     * @return array{start: ?Carbon, end: ?Carbon, label: string}
     */
    private function resolveDateRange(?string $preset, ?string $startRaw, ?string $endRaw): array
    {
        $presetKey = trim((string) $preset);
        $today = Carbon::today();

        if ($presetKey === 'today') {
            return [
                'start' => $today->copy()->startOfDay(),
                'end'   => $today->copy()->endOfDay(),
                'label' => 'Hôm nay (' . $today->format('d/m/Y') . ')',
            ];
        }

        if ($presetKey === '7days') {
            $start = $today->copy()->subDays(6)->startOfDay();
            $end = $today->copy()->endOfDay();

            return [
                'start' => $start,
                'end'   => $end,
                'label' => '7 ngày qua (' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y') . ')',
            ];
        }

        if ($presetKey === 'month') {
            $start = $today->copy()->startOfMonth()->startOfDay();
            $end = $today->copy()->endOfDay();

            return [
                'start' => $start,
                'end'   => $end,
                'label' => 'Tháng này (' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y') . ')',
            ];
        }

        if ($presetKey === 'year') {
            $start = $today->copy()->startOfYear()->startOfDay();
            $end = $today->copy()->endOfDay();

            return [
                'start' => $start,
                'end'   => $end,
                'label' => 'Năm nay (' . $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y') . ')',
            ];
        }

        $parsedStart = $this->parseDateInput($startRaw);
        $parsedEnd = $this->parseDateInput($endRaw);

        if ($parsedStart && $parsedEnd) {
            if ($parsedStart->gt($parsedEnd)) {
                [$parsedStart, $parsedEnd] = [$parsedEnd, $parsedStart];
            }

            return [
                'start' => $parsedStart->copy()->startOfDay(),
                'end'   => $parsedEnd->copy()->endOfDay(),
                'label' => 'Từ ' . $parsedStart->format('d/m/Y') . ' đến ' . $parsedEnd->format('d/m/Y'),
            ];
        }

        if ($parsedStart) {
            return [
                'start' => $parsedStart->copy()->startOfDay(),
                'end'   => $today->copy()->endOfDay(),
                'label' => 'Từ ' . $parsedStart->format('d/m/Y') . ' đến nay',
            ];
        }

        return [
            'start' => null,
            'end'   => null,
            'label' => 'Toàn bộ thời gian',
        ];
    }

    private function parseDateInput(?string $raw): ?Carbon
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
                if ($parsed !== false) {
                    return $parsed;
                }
            } catch (\Throwable) {
                // Thử định dạng tiếp theo
            }
        }

        return null;
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array<int, array{date: string, count: int, revenue: float}>
     */
    private function buildDailyRevenueRows(Collection $orders): array
    {
        $grouped = [];

        foreach ($orders as $order) {
            if ($order->status === 'cancelled' || !$order->created_at) {
                continue;
            }
            $dateKey = $order->created_at->format('Y-m-d');
            if (!isset($grouped[$dateKey])) {
                $grouped[$dateKey] = [
                    'date'    => $order->created_at->format('d/m/Y'),
                    'count'   => 0,
                    'revenue' => 0.0,
                ];
            }
            $grouped[$dateKey]['count']++;
            $grouped[$dateKey]['revenue'] += (float) $order->total_amount;
        }

        ksort($grouped);

        return array_values($grouped);
    }

    /**
     * @return array<int, array{name: string, product_count: int, qty_sold: int, revenue: float}>
     */
    private function buildCategoryRevenueRows(?Carbon $start, ?Carbon $end): array
    {
        $statsQuery = DB::table('categories')
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', function ($join) use ($start, $end) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', '!=', 'cancelled');

                if ($start && $end) {
                    $join->whereBetween('orders.created_at', [$start, $end]);
                }
            })
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw("
                categories.name as name,
                COUNT(DISTINCT products.id) as product_count,
                COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.quantity ELSE 0 END), 0) as qty_sold,
                COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.total_price ELSE 0 END), 0) as revenue
            ")
            ->orderByDesc('revenue')
            ->orderBy('categories.name');

        return $statsQuery->get()
            ->map(fn ($row) => [
                'name'          => (string) $row->name,
                'product_count' => (int) $row->product_count,
                'qty_sold'      => (int) $row->qty_sold,
                'revenue'       => (float) $row->revenue,
            ])
            ->all();
    }

    /**
     * @return array<int, array{
     *     code: string,
     *     name: string,
     *     category_name: string,
     *     price: float,
     *     stock_quantity: int,
     *     qty_sold: int,
     *     revenue: float
     * }>
     */
    private function buildProductRevenueRows(?Carbon $start, ?Carbon $end): array
    {
        $query = Product::query()
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', function ($join) use ($start, $end) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', '!=', 'cancelled');

                if ($start && $end) {
                    $join->whereBetween('orders.created_at', [$start, $end]);
                }
            })
            ->groupBy('products.id', 'products.code', 'products.name', 'categories.name', 'products.price', 'products.stock_quantity', 'products.sold')
            ->selectRaw("
                products.code,
                products.name,
                COALESCE(categories.name, 'Handmade') as category_name,
                products.price,
                products.stock_quantity,
                COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.quantity ELSE 0 END), 0) as qty_sold,
                COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.total_price ELSE 0 END), 0) as revenue
            ")
            ->orderByDesc('revenue')
            ->orderByDesc('qty_sold')
            ->orderBy('products.id');

        return $query->get()
            ->map(fn ($row) => [
                'code'           => (string) $row->code,
                'name'           => (string) $row->name,
                'category_name'  => (string) $row->category_name,
                'price'          => (float) $row->price,
                'stock_quantity' => (int) $row->stock_quantity,
                'qty_sold'       => (int) $row->qty_sold,
                'revenue'        => (float) $row->revenue,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @param  array<int, array{date: string, count: int, revenue: float}>  $dailyRows
     * @param  array<int, array{name: string, product_count: int, qty_sold: int, revenue: float}>  $categoryRows
     * @return array<string, mixed>
     */
    private function buildSummarySheet(
        Collection $orders,
        array $dailyRows,
        array $categoryRows,
        string $rangeLabel,
        string $exportedBy
    ): array {
        $validOrders = $orders->where('status', '!=', 'cancelled');
        $cancelledOrders = $orders->where('status', 'cancelled');

        $validRevenue = (float) $validOrders->sum('total_amount');
        $cancelledRevenue = (float) $cancelledOrders->sum('total_amount');
        $totalShipping = (float) $validOrders->sum('shipping_fee');
        $totalDiscount = (float) $validOrders->sum('discount_amount');

        $roleIdsByName = Role::cachedMap();
        $customerRoleId = array_search(Role::CUSTOMER, $roleIdsByName, true) ?: 2;
        $totalCustomers = User::where('role_id', $customerRoleId)->count();
        $totalProducts = Product::count();

        $rows = [
            [['value' => 'BÁO CÁO DOANH THU - HANDMADE HOUSE', 'style' => XlsxExportService::STYLE_TITLE]],
            [['value' => "Phạm vi: {$rangeLabel} | Người xuất: {$exportedBy} | Ngày xuất: " . now()->format('d/m/Y H:i'), 'style' => XlsxExportService::STYLE_META]],
            [],
            [['value' => 'I. TỔNG QUAN CHỈ SỐ KINH DOANH', 'style' => XlsxExportService::STYLE_SECTION]],
            [
                ['value' => 'Chỉ số thống kê', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Giá trị', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Ghi chú', 'style' => XlsxExportService::STYLE_HEADER],
            ],
            [
                ['value' => 'Tổng doanh thu hợp lệ', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $validRevenue, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_CURRENCY],
                ['value' => 'Không tính đơn hàng đã hủy', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Tổng số đơn hàng hợp lệ', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $validOrders->count(), 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => 'Đơn chờ xác nhận + Đang giao + Hoàn thành', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Đơn hoàn thành', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $orders->where('status', 'completed')->count(), 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => '', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Đơn đang giao', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $orders->where('status', 'shipping')->count(), 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => '', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Đơn chờ xác nhận', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $orders->where('status', 'pending')->count(), 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => '', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Đơn đã hủy', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $cancelledOrders->count(), 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => 'Giá trị đơn hủy: ' . number_format($cancelledRevenue, 0, ',', '.') . 'đ', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Tổng phí vận chuyển (đơn hợp lệ)', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $totalShipping, 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
                ['value' => '', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Tổng giảm giá đã áp dụng', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $totalDiscount, 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
                ['value' => '', 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [
                ['value' => 'Tổng số sản phẩm / Tổng khách hàng', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $totalProducts, 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => "Tổng khách hàng hệ thống: {$totalCustomers}", 'style' => XlsxExportService::STYLE_TEXT],
            ],
            [],
        ];

        $dailySectionRow = count($rows) + 1;
        $rows[] = [['value' => 'II. DOANH THU THEO NGÀY (ĐƠN HÀNG HỢP LỆ)', 'style' => XlsxExportService::STYLE_SECTION]];
        $rows[] = [
            ['value' => 'Ngày', 'style' => XlsxExportService::STYLE_HEADER],
            ['value' => 'Số đơn hợp lệ', 'style' => XlsxExportService::STYLE_HEADER],
            ['value' => 'Doanh thu (VNĐ)', 'style' => XlsxExportService::STYLE_HEADER],
        ];

        if (empty($dailyRows)) {
            $rows[] = [
                ['value' => 'Chưa có đơn hàng hợp lệ trong khoảng thời gian này', 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => 0, 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => 0, 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
            ];
        } else {
            $sumDailyCount = 0;
            $sumDailyRevenue = 0.0;
            foreach ($dailyRows as $daily) {
                $sumDailyCount += $daily['count'];
                $sumDailyRevenue += $daily['revenue'];
                $rows[] = [
                    ['value' => $daily['date'], 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                    ['value' => $daily['count'], 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                    ['value' => $daily['revenue'], 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
                ];
            }
            $rows[] = [
                ['value' => 'Tổng cộng', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
                ['value' => $sumDailyCount, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_INT],
                ['value' => $sumDailyRevenue, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_CURRENCY],
            ];
        }

        $rows[] = [];
        $categorySectionRow = count($rows) + 1;
        $rows[] = [['value' => 'III. DOANH THU THEO DANH MỤC SẢN PHẨM', 'style' => XlsxExportService::STYLE_SECTION]];
        $rows[] = [
            ['value' => 'Danh mục', 'style' => XlsxExportService::STYLE_HEADER],
            ['value' => 'Số sản phẩm', 'style' => XlsxExportService::STYLE_HEADER],
            ['value' => 'Số lượng đã bán', 'style' => XlsxExportService::STYLE_HEADER],
            ['value' => 'Doanh thu sản phẩm (VNĐ)', 'style' => XlsxExportService::STYLE_HEADER],
            ['value' => 'Tỷ trọng doanh thu', 'style' => XlsxExportService::STYLE_HEADER],
        ];

        $totalCatRevenue = array_sum(array_column($categoryRows, 'revenue'));
        $totalCatQty = 0;
        $totalCatProducts = 0;
        foreach ($categoryRows as $cat) {
            $totalCatProducts += $cat['product_count'];
            $totalCatQty += $cat['qty_sold'];
            $share = $totalCatRevenue > 0 ? ($cat['revenue'] / $totalCatRevenue) : 0.0;
            $rows[] = [
                ['value' => $cat['name'], 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $cat['product_count'], 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => $cat['qty_sold'], 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => $cat['revenue'], 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
                ['value' => $share, 'type' => 'n', 'style' => XlsxExportService::STYLE_PERCENT],
            ];
        }

        $rows[] = [
            ['value' => 'Tổng cộng', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => $totalCatProducts, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_INT],
            ['value' => $totalCatQty, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_INT],
            ['value' => $totalCatRevenue, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_CURRENCY],
            ['value' => $totalCatRevenue > 0 ? 1.0 : 0.0, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_PERCENT],
        ];

        return [
            'name'        => 'Tong quan doanh thu',
            'rows'        => $rows,
            'merges'      => [
                'A1:E1',
                'A2:E2',
                'A4:C4',
                "A{$dailySectionRow}:C{$dailySectionRow}",
                "A{$categorySectionRow}:E{$categorySectionRow}",
            ],
            'row_heights' => [
                1 => 28,
                2 => 20,
                4 => 22,
                5 => 24,
                $dailySectionRow => 22,
                ($dailySectionRow + 1) => 24,
                $categorySectionRow => 22,
                ($categorySectionRow + 1) => 24,
            ],
            'min_widths'  => [
                1 => 34,
                2 => 20,
                3 => 28,
                4 => 24,
                5 => 20,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $productRows
     * @return array<string, mixed>
     */
    private function buildProductSheet(array $productRows, string $rangeLabel): array
    {
        $rows = [
            [['value' => 'DOANH THU THEO SẢN PHẨM - HANDMADE HOUSE', 'style' => XlsxExportService::STYLE_TITLE]],
            [['value' => "Phạm vi: {$rangeLabel} | Ngày xuất: " . now()->format('d/m/Y H:i'), 'style' => XlsxExportService::STYLE_META]],
            [],
            [
                ['value' => 'STT', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Mã SP', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Tên sản phẩm', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Danh mục', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Giá bán hiện tại', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Tồn kho', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'SL đã bán (kỳ)', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Doanh thu (VNĐ)', 'style' => XlsxExportService::STYLE_HEADER],
            ],
        ];

        $totalQty = 0;
        $totalRevenue = 0.0;
        foreach ($productRows as $idx => $item) {
            $totalQty += $item['qty_sold'];
            $totalRevenue += $item['revenue'];
            $rows[] = [
                ['value' => $idx + 1, 'type' => 'n', 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                ['value' => $item['code'], 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                ['value' => $item['name'], 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $item['category_name'], 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $item['price'], 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
                ['value' => $item['stock_quantity'], 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => $item['qty_sold'], 'type' => 'n', 'style' => XlsxExportService::STYLE_INT],
                ['value' => $item['revenue'], 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
            ];
        }

        $rows[] = [
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => 'Tổng cộng (' . count($productRows) . ' sản phẩm)', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => $totalQty, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_INT],
            ['value' => $totalRevenue, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_CURRENCY],
        ];

        return [
            'name'        => 'Doanh thu san pham',
            'rows'        => $rows,
            'merges'      => ['A1:H1', 'A2:H2'],
            'row_heights' => [1 => 28, 2 => 20, 4 => 24],
            'min_widths'  => [1 => 8, 2 => 12, 3 => 36, 4 => 18, 5 => 18, 6 => 12, 7 => 16, 8 => 20],
        ];
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array<string, mixed>
     */
    private function buildOrdersSheet(Collection $orders, string $rangeLabel): array
    {
        $statusLabels = [
            'pending'   => 'Chờ xác nhận',
            'shipping'  => 'Đang giao',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        $rows = [
            [['value' => 'CHI TIẾT ĐƠN HÀNG - HANDMADE HOUSE', 'style' => XlsxExportService::STYLE_TITLE]],
            [['value' => "Phạm vi: {$rangeLabel} | Ngày xuất: " . now()->format('d/m/Y H:i'), 'style' => XlsxExportService::STYLE_META]],
            [],
            [
                ['value' => 'STT', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Mã đơn hàng', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Ngày đặt', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Khách hàng', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Số điện thoại', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Phương thức TT', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Trạng thái TT', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Trạng thái đơn', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Phí ship', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Giảm giá', 'style' => XlsxExportService::STYLE_HEADER],
                ['value' => 'Tổng tiền (VNĐ)', 'style' => XlsxExportService::STYLE_HEADER],
            ],
        ];

        $validTotal = 0.0;
        $validShipping = 0.0;
        $validDiscount = 0.0;

        foreach ($orders->values() as $idx => $order) {
            if ($order->status !== 'cancelled') {
                $validTotal += (float) $order->total_amount;
                $validShipping += (float) $order->shipping_fee;
                $validDiscount += (float) $order->discount_amount;
            }

            $rows[] = [
                ['value' => $idx + 1, 'type' => 'n', 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                ['value' => '#' . $order->code, 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                ['value' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : '', 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                ['value' => (string) $order->shipping_name, 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => (string) $order->shipping_phone, 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                ['value' => $order->paymentMethodLabel(), 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $order->paymentStatusLabel(), 'style' => XlsxExportService::STYLE_TEXT],
                ['value' => $statusLabels[$order->status] ?? (string) $order->status, 'style' => XlsxExportService::STYLE_TEXT_CENTER],
                ['value' => (float) $order->shipping_fee, 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
                ['value' => (float) $order->discount_amount, 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
                ['value' => (float) $order->total_amount, 'type' => 'n', 'style' => XlsxExportService::STYLE_CURRENCY],
            ];
        }

        $rows[] = [
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => 'Tổng cộng (chỉ tính đơn hợp lệ, ' . $orders->where('status', '!=', 'cancelled')->count() . '/' . $orders->count() . ' đơn)', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => '', 'style' => XlsxExportService::STYLE_TOTAL_TEXT],
            ['value' => $validShipping, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_CURRENCY],
            ['value' => $validDiscount, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_CURRENCY],
            ['value' => $validTotal, 'type' => 'n', 'style' => XlsxExportService::STYLE_TOTAL_CURRENCY],
        ];

        return [
            'name'        => 'Chi tiet don hang',
            'rows'        => $rows,
            'merges'      => ['A1:K1', 'A2:K2'],
            'row_heights' => [1 => 28, 2 => 20, 4 => 24],
            'min_widths'  => [1 => 8, 2 => 15, 3 => 18, 4 => 24, 5 => 15, 6 => 24, 7 => 22, 8 => 16, 9 => 15, 10 => 15, 11 => 20],
        ];
    }
}
