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
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <button @click="viewOrderDetails({{ $order->id }})" 
                                            class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg transition-all flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        View
                                    </button>

                                    <button @click="openEditModal({{ $order->id }})" 
                                            class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 text-xs font-bold rounded-lg transition-all flex items-center gap-1">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        Edit
                                    </button>

                                    <a href="{{ route('orders.receipt', $order->id) }}" 
                                       target="_blank" 
                                       class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1 shadow-sm">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Reprint
                                    </a>

                                    <button @click="deleteInvoice({{ $order->id }})" 
                                            class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500 text-rose-300 hover:text-white text-xs font-bold rounded-lg transition-all flex items-center gap-1">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        Delete
                                    </button>
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
                        <div x-show="Number(activeOrder.discount_amount) > 0">Discount: <span class="text-rose-400">- <span x-text="currencySymbol"></span> <span x-text="Number(activeOrder.discount_amount).toFixed(2)"></span></span></div>
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

    <!-- EDIT INVOICE MODAL -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 w-full max-w-2xl rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="font-bold text-lg text-white">Edit Invoice</h3>
                    <span class="font-mono text-xs text-brand-400 font-bold" x-text="editInvoiceNumber"></span>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Editable Items -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Invoice Items</label>
                <div class="max-h-56 overflow-y-auto border border-slate-800 rounded-xl p-2 space-y-2">
                    <template x-for="(item, index) in editItems" :key="item.product_id">
                        <div class="flex items-center justify-between gap-2 bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-white text-xs truncate" x-text="item.product_name"></div>
                                <div class="font-mono text-[10px] text-slate-500" x-text="item.item_code + ' · ' + currencySymbol + ' ' + Number(item.unit_price).toFixed(2)"></div>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-900 p-0.5 rounded-lg border border-slate-800">
                                <button @click="changeEditQty(index, -1)" class="w-6 h-6 flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-md font-bold text-xs">-</button>
                                <span class="w-7 text-center font-bold text-xs text-white" x-text="item.quantity"></span>
                                <button @click="changeEditQty(index, 1)" class="w-6 h-6 flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-md font-bold text-xs">+</button>
                            </div>
                            <div class="text-right w-20">
                                <div class="text-emerald-400 font-bold text-xs" x-text="currencySymbol + ' ' + Number(item.quantity * item.unit_price).toFixed(2)"></div>
                            </div>
                            <button @click="removeEditItem(index)" class="p-1.5 bg-slate-900 hover:bg-rose-600 text-slate-400 hover:text-white rounded-lg transition-all" title="Remove item">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </template>
                    <div x-show="editItems.length === 0" class="py-8 text-center text-slate-500 text-xs">
                        No items on this invoice. Add at least one product below.
                    </div>
                </div>

                <!-- Add Product Row -->
                <div class="mt-2 flex flex-col sm:flex-row gap-2">
                    <select x-model="addProductId" class="flex-1 bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                        <option value="">Choose product to add...</option>
                        <template x-for="p in products" :key="p.id">
                            <option :value="p.id" x-text="p.item_code + ' — ' + p.name + ' (' + currencySymbol + ' ' + Number(p.price).toFixed(2) + ')'"></option>
                        </template>
                    </select>
                    <input type="number" min="1" x-model.number="addProductQty" placeholder="Qty"
                           class="w-20 bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                    <button @click="addEditItem()" class="px-3 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Add Item
                    </button>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Payment Method</label>
                    <select x-model="editPaymentMethod" class="w-full bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Amount Paid (<span x-text="currencySymbol"></span>)</label>
                    <input type="number" step="0.01" min="0" x-model.number="editPaidAmount"
                           class="w-full bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Discount -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Discount</label>
                <div class="flex items-center gap-2">
                    <div class="flex bg-slate-950 border border-slate-800 rounded-xl p-0.5 text-[10px] font-bold">
                        <button type="button" @click="editDiscountMode = 'percent'"
                                :class="editDiscountMode === 'percent' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">%</button>
                        <button type="button" @click="editDiscountMode = 'amount'"
                                :class="editDiscountMode === 'amount' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">Amt</button>
                    </div>
                    <input type="number" step="0.01" min="0" x-model.number="editDiscountInput"
                           :placeholder="editDiscountMode === 'percent' ? 'Discount percentage' : 'Discount amount'"
                           class="flex-1 bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Live Totals -->
            <div class="bg-slate-950 p-3 rounded-xl border border-slate-800/80 text-xs space-y-1 text-right">
                <div>Subtotal: <span class="text-slate-300"><span x-text="currencySymbol"></span> <span x-text="Number(editSubtotal).toFixed(2)"></span></span></div>
                <div x-show="editDiscountAmount > 0">Discount: <span class="text-rose-400">- <span x-text="currencySymbol"></span> <span x-text="Number(editDiscountAmount).toFixed(2)"></span></span></div>
                <div>New Total: <strong class="text-emerald-400"><span x-text="currencySymbol"></span> <span x-text="Number(editTotal).toFixed(2)"></span></strong></div>
                <div>Paid Amount: <span class="text-slate-300"><span x-text="currencySymbol"></span> <span x-text="Number(editPaidAmount || 0).toFixed(2)"></span></span></div>
                <div>Change Returned: <span :class="editChange >= 0 ? 'text-emerald-400' : 'text-rose-400'"><span x-text="currencySymbol"></span> <span x-text="Number(Math.max(editChange, 0)).toFixed(2)"></span></span></div>
            </div>

            <!-- Error Banner -->
            <div x-show="editError" x-text="editError" class="p-3 bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold rounded-xl"></div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">
                    Cancel
                </button>
                <button type="button" @click="saveInvoice()" :disabled="isSavingEdit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                    <span x-show="!isSavingEdit" class="flex items-center gap-1">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        Save Changes
                    </span>
                    <span x-show="isSavingEdit" class="flex items-center gap-1">
                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                        Saving...
                    </span>
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
            showEditModal: false,
            editOrderId: null,
            editInvoiceNumber: '',
            editItems: [],
            editPaidAmount: 0,
            editPaymentMethod: 'cash',
            editDiscountMode: 'amount',
            editDiscountInput: 0,
            addProductId: '',
            addProductQty: 1,
            isSavingEdit: false,
            editError: '',
            currencySymbol: "{{ $shopSettings['currency_symbol'] ?? 'LKR' }}",
            products: @json($products),
            csrfToken: "{{ csrf_token() }}",

            get editSubtotal() {
                return this.editItems.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
            },

            get editDiscountInputValue() {
                return parseFloat(this.editDiscountInput) || 0;
            },

            get editDiscountAmount() {
                if (this.editDiscountInputValue <= 0) return 0;
                let amount;
                if (this.editDiscountMode === 'percent') {
                    amount = (this.editSubtotal * this.editDiscountInputValue) / 100;
                } else {
                    amount = this.editDiscountInputValue;
                }
                amount = Math.min(amount, this.editSubtotal);
                return Math.round(amount * 100) / 100;
            },

            get editTotal() {
                return Math.max(0, Math.round((this.editSubtotal - this.editDiscountAmount) * 100) / 100);
            },

            get editChange() {
                return (parseFloat(this.editPaidAmount) || 0) - this.editTotal;
            },

            viewOrderDetails(orderId) {
                fetch(`/admin/sales/${orderId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.activeOrder = data.order;
                            this.showModal = true;
                        }
                    });
            },

            openEditModal(orderId) {
                fetch(`/admin/sales/${orderId}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const order = data.order;
                            this.editOrderId = order.id;
                            this.editInvoiceNumber = order.invoice_number;
                            this.editItems = order.items.map(i => ({
                                product_id: i.product_id,
                                product_name: i.product_name,
                                item_code: i.item_code,
                                unit_price: parseFloat(i.unit_price),
                                quantity: i.quantity
                            }));
                            this.editPaidAmount = parseFloat(order.paid_amount);
                            this.editPaymentMethod = order.payment_method;
                            this.editDiscountMode = order.discount_percent && order.discount_percent > 0 ? 'percent' : 'amount';
                            this.editDiscountInput = order.discount_percent && order.discount_percent > 0 ? parseFloat(order.discount_percent) : parseFloat(order.discount_amount || 0);
                            this.addProductId = '';
                            this.addProductQty = 1;
                            this.editError = '';
                            this.showEditModal = true;
                        }
                    });
            },

            changeEditQty(index, delta) {
                const newQty = this.editItems[index].quantity + delta;
                if (newQty < 1) return;
                this.editItems[index].quantity = newQty;
            },

            removeEditItem(index) {
                this.editItems.splice(index, 1);
            },

            addEditItem() {
                if (!this.addProductId) return;
                const product = this.products.find(p => p.id === Number(this.addProductId));
                if (!product) return;

                const existing = this.editItems.find(i => i.product_id === product.id);
                if (existing) {
                    existing.quantity += Math.max(1, this.addProductQty || 1);
                } else {
                    this.editItems.push({
                        product_id: product.id,
                        product_name: product.name,
                        item_code: product.item_code,
                        unit_price: parseFloat(product.price),
                        quantity: Math.max(1, this.addProductQty || 1)
                    });
                }
                this.addProductId = '';
                this.addProductQty = 1;
            },

            saveInvoice() {
                if (this.editItems.length === 0) {
                    this.editError = 'Invoice must contain at least one item.';
                    return;
                }
                if ((parseFloat(this.editPaidAmount) || 0) < this.editTotal) {
                    this.editError = 'Paid amount cannot be less than the invoice total.';
                    return;
                }

                this.isSavingEdit = true;
                this.editError = '';

                fetch(`/admin/sales/${this.editOrderId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        items: this.editItems.map(i => ({ id: i.product_id, quantity: i.quantity })),
                        paid_amount: this.editPaidAmount,
                        payment_method: this.editPaymentMethod,
                        discount_percent: this.editDiscountMode === 'percent' && this.editDiscountInputValue > 0 ? this.editDiscountInputValue : null,
                        discount_amount: this.editDiscountAmount
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isSavingEdit = false;
                    if (data.success) {
                        window.location.reload();
                    } else {
                        this.editError = data.message || 'Failed to update invoice.';
                    }
                })
                .catch(() => {
                    this.isSavingEdit = false;
                    this.editError = 'Network error updating invoice.';
                });
            },

            deleteInvoice(orderId) {
                if (!confirm('Delete this invoice permanently? Its items will be returned to stock.')) return;

                fetch(`/admin/sales/${orderId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to delete invoice.');
                    }
                });
            }
        };
    }
</script>
@endpush
