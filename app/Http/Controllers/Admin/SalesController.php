<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $cashiers = User::where('role', 'cashier')->get();
        $query = Order::with(['cashier', 'items']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        $totalSalesSum = (clone $query)->sum('total_amount');

        return view('admin.sales.index', compact('orders', 'cashiers', 'totalSalesSum'));
    }

    public function show(Order $order)
    {
        $order->load(['cashier', 'items.product']);
        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }
}
