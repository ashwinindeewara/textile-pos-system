<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function receipt(Order $order)
    {
        $order->load(['cashier', 'items']);
        $shopSettings = Setting::getAllSettings();
        return view('orders.receipt', compact('order', 'shopSettings'));
    }
}
