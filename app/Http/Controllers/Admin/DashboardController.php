<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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

        return view('admin.dashboard', compact(
            'revenueToday',
            'newOrdersCount',
            'processingOrdersCount',
            'lowStockProducts',
            'lowStockCount',
            'newCustomersCount',
            'totalProducts',
            'recentOrders'
        ));
    }
}
