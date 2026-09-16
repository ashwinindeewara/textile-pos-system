<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Update a completed invoice: replace items, adjust stock for quantity
     * differences, remove lines, and recompute payment totals.
     */
    public function update(Order $order, array $items, $paidAmount, $paymentMethod)
    {
        if ($order->status !== 'completed') {
            throw new \Exception('Only completed invoices can be edited.');
        }

        $order->load('items');

        $paidAmount = (float) $paidAmount;
        $paymentMethod = $paymentMethod ?: $order->payment_method;

        DB::transaction(function () use ($order, $items, $paidAmount, $paymentMethod) {
            $existingItems = $order->items->keyBy('product_id');
            $newProductIds = array_column($items, 'id');

            $totalAmount = 0;
            $newItems = [];

            foreach ($items as $item) {
                $product = Product::where('id', $item['id'])->lockForUpdate()->firstOrFail();
                $newQty = max(1, (int) $item['quantity']);
                $prevQty = $existingItems->has($product->id) ? $existingItems[$product->id]->quantity : 0;
                $stockDiff = $newQty - $prevQty;

                if ($product->stock_qty - $stockDiff < 0) {
                    throw new \Exception("Insufficient stock for '{$product->name}': only {$product->stock_qty} available.");
                }

                $product->stock_qty -= $stockDiff;
                $product->save();

                $unitPrice = (float) $product->price;
                $subtotal = $unitPrice * $newQty;
                $totalAmount += $subtotal;

                $newItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'item_code' => $product->item_code,
                    'unit_price' => $unitPrice,
                    'quantity' => $newQty,
                    'subtotal' => $subtotal,
                ];
            }

            // Restore stock for lines that were removed entirely
            foreach ($existingItems as $productId => $existingItem) {
                if (!in_array($productId, $newProductIds)) {
                    $product = Product::find($productId);
                    if ($product) {
                        $product->stock_qty += $existingItem->quantity;
                        $product->save();
                    }
                }
            }

            if ($paidAmount < $totalAmount) {
                throw new \Exception('Insufficient payment: Total is LKR ' . number_format($totalAmount, 2) . ', but paid amount is LKR ' . number_format($paidAmount, 2));
            }

            $order->items()->delete();
            foreach ($newItems as $itemData) {
                OrderItem::create($itemData);
            }

            $order->update([
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'change_amount' => $paidAmount - $totalAmount,
                'payment_method' => $paymentMethod,
            ]);
        });

        return $order->fresh('items');
    }

    /**
     * Delete a completed invoice and return the items back to stock.
     */
    public function delete(Order $order)
    {
        if ($order->status !== 'completed') {
            throw new \Exception('Only completed invoices can be deleted.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->stock_qty += $item->quantity;
                    $product->save();
                }
            }

            $order->items()->delete();
            $order->delete();
        });
    }
}