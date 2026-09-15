<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        
        $query = Product::with('category')->where('stock_qty', '>', 0);

        if ($request->has('category') && $request->category != 'all') {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function lookup(Request $request)
    {
        $code = trim($request->input('code'));
        
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'Item code required.']);
        }

        $product = Product::with('category')
            ->where('item_code', $code)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => "Item code '{$code}' not found."]);
        }

        if ($product->stock_qty <= 0) {
            return response()->json(['success' => false, 'message' => "Product '{$product->name}' is out of stock!"]);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'item_code' => $product->item_code,
                'price' => (float) $product->price,
                'stock_qty' => $product->stock_qty,
                'category_name' => $product->category ? $product->category->name : 'General',
            ]
        ]);
    }

    /**
     * Hold / Park an order temporarily without deducting stock.
     */
    public function holdOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $cartItems = $request->input('items');
        $cashierId = Auth::id();

        try {
            $heldOrder = DB::transaction(function () use ($cartItems, $cashierId) {
                $totalAmount = 0;
                $itemsToSave = [];

                foreach ($cartItems as $item) {
                    $product = Product::where('id', $item['id'])->firstOrFail();
                    $reqQty = (int) $item['quantity'];
                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $reqQty;
                    $totalAmount += $subtotal;

                    $itemsToSave[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'item_code' => $product->item_code,
                        'unit_price' => $unitPrice,
                        'quantity' => $reqQty,
                        'subtotal' => $subtotal,
                    ];
                }

                $invoiceNumber = 'HOLD-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                $order = Order::create([
                    'invoice_number' => $invoiceNumber,
                    'cashier_id' => $cashierId,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0.00,
                    'change_amount' => 0.00,
                    'payment_method' => 'cash',
                    'status' => 'held',
                ]);

                foreach ($itemsToSave as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Order parked on hold successfully!',
                'hold_id' => $heldOrder->id,
                'invoice_number' => $heldOrder->invoice_number,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error holding order: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Fetch list of all currently held / parked orders.
     */
    public function getHeldOrders()
    {
        $heldOrders = Order::with(['items', 'cashier'])
            ->where('status', 'held')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'orders' => $heldOrders,
        ]);
    }

    /**
     * Recall a held order back into the active cart and remove held record.
     */
    public function recallHeldOrder(Order $order)
    {
        if ($order->status !== 'held') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not in held status.',
            ], 400);
        }

        $order->load(['items.product']);

        $cartItems = [];
        foreach ($order->items as $item) {
            $product = $item->product;
            $stockQty = $product ? $product->stock_qty : 0;

            $cartItems[] = [
                'id' => $item->product_id,
                'name' => $item->product_name,
                'item_code' => $item->item_code,
                'price' => (float) $item->unit_price,
                'stock_qty' => $stockQty,
                'quantity' => $item->quantity,
            ];
        }

        // Delete the held order so it is now cleanly loaded into the active cart
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order recalled successfully!',
            'items' => $cartItems,
        ]);
    }

    /**
     * Delete a held order permanently.
     */
    public function deleteHeldOrder(Order $order)
    {
        if ($order->status !== 'held') {
            return response()->json([
                'success' => false,
                'message' => 'Order is not in held status.',
            ], 400);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Held order deleted.',
        ]);
    }

    /**
     * Complete checkout with strict lockForUpdate transaction and stock deduction.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,card',
        ]);

        $cartItems = $request->input('items');
        $paidAmount = (float) $request->input('paid_amount');
        $paymentMethod = $request->input('payment_method');
        $cashierId = Auth::id();

        try {
            $order = DB::transaction(function () use ($cartItems, $paidAmount, $paymentMethod, $cashierId) {
                $totalAmount = 0;
                $itemsToSave = [];

                foreach ($cartItems as $item) {
                    // Lock product record for update to prevent race conditions
                    $product = Product::where('id', $item['id'])->lockForUpdate()->firstOrFail();

                    $reqQty = (int) $item['quantity'];

                    if ($product->stock_qty < $reqQty) {
                        throw new \Exception("Stock error: Only {$product->stock_qty} unit(s) remaining for '{$product->name}'.");
                    }

                    $unitPrice = (float) $product->price;
                    $subtotal = $unitPrice * $reqQty;
                    $totalAmount += $subtotal;

                    // Deduct stock directly on product record
                    $product->stock_qty -= $reqQty;
                    $product->save();

                    $itemsToSave[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'item_code' => $product->item_code,
                        'unit_price' => $unitPrice,
                        'quantity' => $reqQty,
                        'subtotal' => $subtotal,
                    ];
                }

                if ($paidAmount < $totalAmount) {
                    throw new \Exception("Insufficient payment: Total is LKR " . number_format($totalAmount, 2) . ", but paid amount is LKR " . number_format($paidAmount, 2));
                }

                $changeAmount = $paidAmount - $totalAmount;

                // Unique invoice number generation
                $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                $order = Order::create([
                    'invoice_number' => $invoiceNumber,
                    'cashier_id' => $cashierId,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                ]);

                foreach ($itemsToSave as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                return $order;
            });

            return response()->json([
                'success' => true,
                'message' => 'Checkout completed successfully!',
                'order_id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'receipt_url' => route('orders.receipt', $order->id),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
