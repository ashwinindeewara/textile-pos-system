<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');
        
        $todaySales = Order::whereDate('created_at', $today)->sum('total_amount');
        $todayOrdersCount = Order::whereDate('created_at', $today)->count();
        $totalProductsCount = Product::count();
        $lowStockProducts = Product::with('category')->where('stock_qty', '<=', 5)->get();

        $recentOrders = Order::with('cashier')->orderBy('created_at', 'desc')->take(8)->get();

        // Top Selling Categories
        $topCategories = Category::withCount('products')->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'todayOrdersCount',
            'totalProductsCount',
            'lowStockProducts',
            'recentOrders',
            'topCategories'
        ));
    }
}
