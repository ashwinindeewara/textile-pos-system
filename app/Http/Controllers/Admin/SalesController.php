<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $cashiers = User::where('role', 'cashier')->get();
        $query = Order::with(['cashier', 'items'])->where('status', 'completed');

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
        $products = Product::orderBy('name')->get();

        return view('admin.sales.index', compact('orders', 'cashiers', 'totalSalesSum', 'products'));
    }

    public function show(Order $order)
    {
        $order->load(['cashier', 'items.product']);
        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $order = app(InvoiceService::class)->update(
                $order,
                $request->input('items'),
                $request->input('paid_amount'),
                $request->input('payment_method'),
                $request->input('discount_percent'),
                $request->input('discount_amount')
            );

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Order $order)
    {
        try {
            app(InvoiceService::class)->delete($order);

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted and stock restored.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
