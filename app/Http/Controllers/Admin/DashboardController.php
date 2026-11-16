<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\RevenueReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DashboardController extends Controller
{
    public function index(): View
    {
        $dashboardData = Cache::remember('admin_dashboard_stats', 60, function () {
            $orderSummary = Order::query()
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END), 0) as revenue_total,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_count,
                    COALESCE(SUM(CASE WHEN status = 'shipping' THEN 1 ELSE 0 END), 0) as shipping_count
                ")
                ->first();

            $revenueToday = (float) ($orderSummary->revenue_total ?? 0);
            $newOrdersCount = (int) ($orderSummary->pending_count ?? 0);
            $processingOrdersCount = (int) ($orderSummary->shipping_count ?? 0);

            $productSummary = Product::query()
                ->selectRaw("
                    COUNT(*) as total_products,
                    COALESCE(SUM(CASE WHEN stock_quantity <= min_stock_threshold THEN 1 ELSE 0 END), 0) as low_stock_count
                ")
                ->first();

            $totalProducts = (int) ($productSummary->total_products ?? 0);
            $lowStockCount = (int) ($productSummary->low_stock_count ?? 0);
            $lowStockProducts = Product::whereColumn('stock_quantity', '<=', 'min_stock_threshold')->take(3)->get();

            $roleIdsByName = Role::cachedMap();
            $customerRoleId = array_search(Role::CUSTOMER, $roleIdsByName, true) ?: 2;
            $newCustomersCount = User::where('role_id', $customerRoleId)->count();

            $recentOrders = Order::latest('id')->take(5)->get();

            // 1. Thống kê Top mặt hàng bán chạy (kèm doanh thu thực tế từ order_items hợp lệ)
            $topSellingProducts = Product::with('category')
                ->withSum(['orderItems as valid_ordered_qty' => function ($q) {
                    $q->whereHas('order', fn ($oq) => $oq->where('status', '!=', 'cancelled'));
                }], 'quantity')
                ->withSum(['orderItems as total_revenue' => function ($q) {
                    $q->whereHas('order', fn ($oq) => $oq->where('status', '!=', 'cancelled'));
                }], 'total_price')
                ->orderByDesc('sold')
                ->orderByDesc('total_revenue')
                ->take(5)
                ->get();

            // 2. Gộp doanh thu theo ngày cho cả biểu đồ 7 ngày và 4 tuần gần nhất (1 query duy nhất)
            $rangeStart = Carbon::today()->subWeeks(3)->startOfWeek()->startOfDay();
            $sevenDaysStart = Carbon::today()->subDays(6)->startOfDay();
            $queryStart = $rangeStart->lt($sevenDaysStart) ? $rangeStart : $sevenDaysStart;
            $queryEnd = Carbon::today()->endOfWeek()->endOfDay();

            $dailyRevenues = Order::query()
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$queryStart, $queryEnd])
                ->selectRaw('DATE(created_at) as order_date, COALESCE(SUM(total_amount), 0) as total')
                ->groupByRaw('DATE(created_at)')
                ->pluck('total', 'order_date');

            $weekLabels = [];
            $weekData = [];
            $dayNames = [
                1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5',
                5 => 'T6', 6 => 'T7', 0 => 'CN',
            ];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $weekLabels[] = $dayNames[$date->dayOfWeek] . ' (' . $date->format('d/m') . ')';
                $dailySum = (float) ($dailyRevenues[$date->toDateString()] ?? 0);
                $weekData[] = round($dailySum / 1000000, 2);
            }
            $revenueWeekChart = [
                'labels' => $weekLabels,
                'data'   => $weekData,
            ];

            $monthLabels = [];
            $monthData = [];
            for ($w = 3; $w >= 0; $w--) {
                $startOfWeek = Carbon::today()->subWeeks($w)->startOfWeek();
                $endOfWeek = Carbon::today()->subWeeks($w)->endOfWeek();
                $monthLabels[] = 'Tuần ' . (4 - $w) . ' (' . $startOfWeek->format('d/m') . ')';

                $weeklySum = 0.0;
                $cursor = $startOfWeek->copy();
                while ($cursor->lte($endOfWeek)) {
                    $weeklySum += (float) ($dailyRevenues[$cursor->toDateString()] ?? 0);
                    $cursor->addDay();
                }
                $monthData[] = round($weeklySum / 1000000, 2);
            }
            $revenueMonthChart = [
                'labels' => $monthLabels,
                'data'   => $monthData,
            ];

            // 3. Dữ liệu động cho Biểu đồ tỷ trọng theo danh mục
            $categoriesStats = Category::withCount('products')
                ->withSum('products as total_sold', 'sold')
                ->get();

            $totalSoldAll = max(1, (int) $categoriesStats->sum('total_sold'));
            $categoryShareChart = [
                'labels'      => $categoriesStats->pluck('name')->values()->all(),
                'percentages' => $categoriesStats->map(fn ($c) => round(((int) $c->total_sold / $totalSoldAll) * 100, 1))->values()->all(),
                'sold_counts' => $categoriesStats->map(fn ($c) => (int) $c->total_sold)->values()->all(),
            ];

            return compact(
                'revenueToday',
                'newOrdersCount',
                'processingOrdersCount',
                'lowStockProducts',
                'lowStockCount',
                'newCustomersCount',
                'totalProducts',
                'recentOrders',
                'topSellingProducts',
                'revenueWeekChart',
                'revenueMonthChart',
                'categoryShareChart'
            );
        });

        return view('admin.dashboard', $dashboardData);
    }

    /**
     * Xuất báo cáo doanh thu ra file Excel (.xlsx).
     */
    public function exportRevenue(Request $request, RevenueReportService $revenueReportService): BinaryFileResponse
    {
        $exportedBy = Auth::user()->full_name ?? Auth::user()->username ?? 'Admin';
        $report = $revenueReportService->exportRevenueXlsx([
            'preset'     => $request->query('preset'),
            'start_date' => $request->query('start_date'),
            'end_date'   => $request->query('end_date'),
        ], (string) $exportedBy);

        return response()->download($report['path'], $report['filename'], [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
