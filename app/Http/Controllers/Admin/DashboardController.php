<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $revenueToday = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $newOrdersCount = Order::where('status', 'pending')->count();
        $processingOrdersCount = Order::where('status', 'shipping')->count();
        $lowStockProducts = Product::where('stock_quantity', '<=', 5)->take(3)->get();
        $lowStockCount = Product::where('stock_quantity', '<=', 5)->count();
        $newCustomersCount = User::where('role_id', 2)->count();
        $totalProducts = Product::count();
        $recentOrders = Order::latest()->take(5)->get();

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

        // 2. Dữ liệu động cho Biểu đồ doanh thu 7 ngày gần nhất (đơn vị: triệu VNĐ)
        $weekLabels = [];
        $weekData = [];
        $dayNames = [
            1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5',
            5 => 'T6', 6 => 'T7', 0 => 'CN',
        ];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weekLabels[] = $dayNames[$date->dayOfWeek] . ' (' . $date->format('d/m') . ')';
            $dailySum = (float) Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date->toDateString())
                ->sum('total_amount');
            $weekData[] = round($dailySum / 1000000, 2);
        }
        $revenueWeekChart = [
            'labels' => $weekLabels,
            'data'   => $weekData,
        ];

        // 3. Dữ liệu động cho Biểu đồ doanh thu 4 tuần gần nhất (đơn vị: triệu VNĐ)
        $monthLabels = [];
        $monthData = [];
        for ($w = 3; $w >= 0; $w--) {
            $startOfWeek = Carbon::today()->subWeeks($w)->startOfWeek();
            $endOfWeek = Carbon::today()->subWeeks($w)->endOfWeek();
            $monthLabels[] = 'Tuần ' . (4 - $w) . ' (' . $startOfWeek->format('d/m') . ')';
            $weeklySum = (float) Order::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
                ->sum('total_amount');
            $monthData[] = round($weeklySum / 1000000, 2);
        }
        $revenueMonthChart = [
            'labels' => $monthLabels,
            'data'   => $monthData,
        ];

        // 4. Dữ liệu động cho Biểu đồ tỷ trọng theo danh mục
        $categoriesStats = Category::withCount('products')
            ->withSum('products as total_sold', 'sold')
            ->get();

        $totalSoldAll = max(1, (int) $categoriesStats->sum('total_sold'));
        $categoryShareChart = [
            'labels'      => $categoriesStats->pluck('name')->values()->all(),
            'percentages' => $categoriesStats->map(fn ($c) => round(((int) $c->total_sold / $totalSoldAll) * 100, 1))->values()->all(),
            'sold_counts' => $categoriesStats->map(fn ($c) => (int) $c->total_sold)->values()->all(),
        ];

        return view('admin.dashboard', compact(
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
        ));
    }
}
