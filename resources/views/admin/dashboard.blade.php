@extends('layouts.app', ['title' => 'Admin Dashboard - Textile POS'])

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    <!-- Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Admin Overview</h1>
            <p class="text-sm text-slate-400 mt-1">Real-time inventory metrics, daily sales summary, and stock alerts.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('pos.index') }}" 
               class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                Open POS Counter
            </a>
            <a href="{{ route('admin.products.index') }}" 
               class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl border border-slate-700 transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add New Product
            </a>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: Today's Sales -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Today's Revenue</span>
                <span class="text-2xl font-extrabold text-emerald-400 mt-1 block">{{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($todaySales, 2) }}</span>
                <span class="text-[11px] text-slate-500 mt-1 block">{{ $todayOrdersCount }} orders today</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="dollar-sign" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 2: Today's Orders -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Completed Orders</span>
                <span class="text-2xl font-extrabold text-white mt-1 block">{{ number_format($todayOrdersCount) }}</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Invoices generated</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-brand-500/10 border border-brand-500/20 text-brand-400 flex items-center justify-center">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 3: Total Products -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Active Inventory</span>
                <span class="text-2xl font-extrabold text-white mt-1 block">{{ number_format($totalProductsCount) }}</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Textile items listed</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 4: Low Stock Alert -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Low Stock Alert</span>
                <span class="text-2xl font-extrabold text-rose-400 mt-1 block">{{ count($lowStockProducts) }}</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Items ≤ 5 stock</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Low Stock & Recent Sales -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Low Stock Warnings Box -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400"></i>
                        <h2 class="font-bold text-white text-base">Low Stock Warnings (≤ 5 units)</h2>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="text-xs text-brand-400 hover:underline font-semibold">Manage Stock</a>
                </div>

                @if($lowStockProducts->count() > 0)
                    <div class="space-y-2.5">
                        @foreach($lowStockProducts as $prod)
                            <div class="p-3 bg-slate-950/80 border border-rose-500/20 rounded-xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="font-mono text-xs font-bold text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded">
                                        {{ $prod->item_code }}
                                    </span>
                                    <div>
                                        <h4 class="font-semibold text-sm text-white">{{ $prod->name }}</h4>
                                        <span class="text-xs text-slate-400">{{ optional($prod->category)->name }}</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2.5 py-1 bg-rose-500/20 text-rose-300 font-bold text-xs rounded-lg border border-rose-500/30">
                                        {{ $prod->stock_qty }} left
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-slate-500 text-sm">
                        <i data-lucide="check-circle-2" class="w-8 h-8 text-emerald-400 mx-auto mb-2"></i>
                        All products have sufficient stock!
                    </div>
                @endif
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                    <h2 class="font-bold text-white text-base flex items-center gap-2">
                        <i data-lucide="history" class="w-5 h-5 text-brand-400"></i>
                        Recent Sales Transactions
                    </h2>
                    <a href="{{ route('admin.sales.index') }}" class="text-xs text-brand-400 hover:underline font-semibold">View All Sales</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="text-xs uppercase bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">Invoice #</th>
                                <th class="p-3">Cashier</th>
                                <th class="p-3">Date & Time</th>
                                <th class="p-3 text-right">Amount</th>
                                <th class="p-3 text-center">Receipt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <td class="p-3 font-mono font-bold text-white">{{ $order->invoice_number }}</td>
                                    <td class="p-3 font-medium">{{ optional($order->cashier)->name }}</td>
                                    <td class="p-3 text-xs text-slate-400">{{ $order->created_at->format('M d, H:i') }}</td>
                                    <td class="p-3 text-right font-extrabold text-emerald-400">{{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($order->total_amount, 2) }}</td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('orders.receipt', $order->id) }}" target="_blank" class="p-1.5 bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white rounded-lg inline-block transition-all">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Categories Breakdown & Quick Actions -->
        <div class="space-y-8">
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
                <h2 class="font-bold text-white text-base mb-4 pb-3 border-b border-slate-800 flex items-center gap-2">
                    <i data-lucide="tags" class="w-5 h-5 text-brand-400"></i>
                    Categories Overview
                </h2>

                <div class="space-y-3">
                    @foreach($topCategories as $cat)
                        <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl flex items-center justify-between">
                            <span class="font-semibold text-sm text-slate-200">{{ $cat->name }}</span>
                            <span class="px-2.5 py-0.5 bg-brand-500/10 text-brand-300 font-bold text-xs rounded-full">
                                {{ $cat->products_count }} products
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
