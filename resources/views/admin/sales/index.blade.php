@extends('layouts.app', ['title' => 'Sales History - Textile POS'])

@section('content')
<div x-data="salesHistory()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header & Total Sales Summary Card -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Sales History & Transactions</h1>
            <p class="text-sm text-slate-400 mt-1">Review completed customer orders, cashier performance, and reprint thermal bills.</p>
        </div>

        <div class="px-5 py-3 bg-slate-900 border border-slate-800 rounded-2xl flex items-center gap-4 shadow-xl">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Filtered Total Revenue</span>
                <span class="text-xl font-extrabold text-emerald-400">{{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($totalSalesSum, 2) }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <i data-lucide="dollar-sign" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl shadow-xl">
        <form method="GET" action="{{ route('admin.sales.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            
            <!-- Date Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">Filter Date</label>
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}"
                       onchange="this.form.submit()"
                       class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
            </div>

            <!-- Cashier Filter -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">Cashier</label>
                <select name="cashier_id" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-800 text-slate-200 text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                    <option value="">All Cashiers</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c->id }}" {{ request('cashier_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Invoice Search -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">Search Invoice #</label>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="e.g. INV-2026..."
                       class="w-full bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all">
                    Apply Filter
                </button>
                @if(request()->hasAny(['date', 'cashier_id', 'search']))
                    <a href="{{ route('admin.sales.index') }}" class="px-3 py-2 bg-slate-950 text-slate-400 hover:text-white text-xs font-bold rounded-xl">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-4">Invoice #</th>
                        <th class="p-4">Cashier</th>
                        <th class="p-4">Date & Time</th>
                        <th class="p-4 text-center">Payment</th>
                        <th class="p-4 text-center">Items Count</th>
                        <th class="p-4 text-right">Total Amount</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-4 font-mono font-bold text-white">
                                {{ $order->invoice_number }}
                            </td>
                            <td class="p-4 font-semibold text-slate-200">
                                {{ optional($order->cashier)->name ?? 'Cashier' }}
                            </td>
                            <td class="p-4 text-xs text-slate-400">
                                {{ $order->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-0.5 uppercase text-[10px] font-bold rounded-full {{ $order->payment_method === 'cash' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' }}">
                                    {{ $order->payment_method }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-bold text-slate-300">
                                {{ $order->items->sum('quantity') }}
                            </td>
                            <td class="p-4 text-right font-extrabold text-emerald-400 text-base">
                                {{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="viewOrderDetails({{ $order->id }})" 
                                            class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg transition-all flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        View
                                    </button>

                                    <a href="{{ route('orders.receipt', $order->id) }}" 
                                       target="_blank" 
                                       class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1 shadow-sm">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Reprint Bill
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 text-sm">
                                <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                No sales transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- ORDER DETAILS MODAL -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-slate-900 border border-slate-800 w-full max-w-2xl rounded-3xl p-6 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="font-bold text-lg text-white">Invoice Details</h3>
                    <span class="font-mono text-xs text-brand-400 font-bold" x-text="activeOrder?.invoice_number"></span>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <template x-if="activeOrder">
                <div class="space-y-4">
                    <div class="grid grid-cols-3 gap-3 bg-slate-950 p-3 rounded-xl border border-slate-800/80 text-xs">
                        <div>
                            <span class="text-slate-400 block">Cashier:</span>
                            <span class="font-bold text-white" x-text="activeOrder.cashier?.name || 'Cashier'"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Payment Method:</span>
                            <span class="font-bold uppercase text-brand-400" x-text="activeOrder.payment_method"></span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Date & Time:</span>
                            <span class="font-bold text-slate-300" x-text="new Date(activeOrder.created_at).toLocaleString()"></span>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="max-h-60 overflow-y-auto border border-slate-800 rounded-xl">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950 text-slate-400 uppercase">
                                <tr>
                                    <th class="p-2.5">Item Name</th>
                                    <th class="p-2.5 text-center">Qty</th>
                                    <th class="p-2.5 text-right">Price</th>
                                    <th class="p-2.5 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <template x-for="item in activeOrder.items" :key="item.id">
                                    <tr>
                                        <td class="p-2.5 font-semibold text-white">
                                            <span x-text="item.product_name"></span>
                                            <span class="font-mono text-[10px] text-slate-500 block" x-text="item.item_code"></span>
                                        </td>
                                        <td class="p-2.5 text-center font-bold" x-text="item.quantity"></td>
                                        <td class="p-2.5 text-right" x-text="currencySymbol + ' ' + Number(item.unit_price).toFixed(2)"></td>
                                        <td class="p-2.5 text-right font-bold text-emerald-400" x-text="currencySymbol + ' ' + Number(item.subtotal).toFixed(2)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="bg-slate-950 p-3 rounded-xl border border-slate-800/80 text-xs space-y-1 text-right">
                        <div>Total Amount: <strong class="text-sm text-emerald-400"><span x-text="currencySymbol"></span> <span x-text="Number(activeOrder.total_amount).toFixed(2)"></span></strong></div>
                        <div>Paid Amount: <span class="text-slate-300"><span x-text="currencySymbol"></span> <span x-text="Number(activeOrder.paid_amount).toFixed(2)"></span></span></div>
                        <div>Change Returned: <span class="text-slate-300"><span x-text="currencySymbol"></span> <span x-text="Number(activeOrder.change_amount).toFixed(2)"></span></span></div>
                    </div>
                </div>
            </template>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">
                    Close
                </button>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function salesHistory() {
        return {
            showModal: false,
            activeOrder: null,
            currencySymbol: "{{ $shopSettings['currency_symbol'] ?? 'LKR' }}",

            viewOrderDetails(orderId) {
                fetch(`/admin/sales/${orderId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.activeOrder = data.order;
                            this.showModal = true;
                        }
                    });
            }
        };
    }
</script>
@endpush
