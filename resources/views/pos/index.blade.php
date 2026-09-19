@extends('layouts.app', ['title' => 'Cashier POS Billing Terminal'])

@section('content')
<div x-data="posSystem()" x-init="init()" class="md:h-[calc(100vh-4rem)] flex flex-col md:flex-row md:overflow-hidden bg-slate-950">

    <!-- LEFT SIDE: Product Catalog & Item Code Scanner -->
    <div class="flex-1 flex flex-col md:h-full md:overflow-hidden border-r border-slate-800/80">
        
        <!-- Top Toolbar & Barcode Input -->
        <div class="p-4 bg-slate-900 border-b border-slate-800 flex flex-col sm:flex-row gap-3 items-center justify-between">
            
            <!-- Barcode / Item Code Scanner Box -->
            <form @submit.prevent="scanItemCode()" class="w-full sm:w-80 relative">
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-brand-400">
                        <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                    </div>
                    <input type="text" 
                           x-ref="barcodeInput"
                           x-model="itemCodeInput"
                           placeholder="Scan or type Item Code (e.g. DNM-001)..."
                           class="w-full pl-11 pr-20 py-2.5 bg-slate-950 border-2 border-brand-500/50 focus:border-brand-500 text-white rounded-xl text-sm font-semibold tracking-wide placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30 transition-all">
                    <button type="submit" 
                            class="absolute right-1.5 px-3 py-1 bg-brand-600 hover:bg-brand-500 text-white font-semibold text-xs rounded-lg transition-all shadow-sm">
                        Scan / Add
                    </button>
                </div>
            </form>

            <!-- Search Input & Held Orders Trigger Button -->
            <div class="w-full sm:w-auto flex items-center gap-3">
                <div class="relative flex-1 sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Filter by product name..."
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 text-slate-200 rounded-xl text-sm focus:outline-none focus:border-slate-600 transition-all">
                </div>

                <!-- Recall / Held Orders Button -->
                <button type="button" 
                        @click="openHeldModal()"
                        class="px-3.5 py-2.5 bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/20 text-amber-300 font-bold rounded-xl text-xs flex items-center gap-2 transition-all relative">
                    <i data-lucide="pause-circle" class="w-4 h-4 text-amber-400"></i>
                    <span>Held Bills</span>
                    <span x-show="heldOrders.length > 0" 
                          class="px-2 py-0.5 bg-amber-500 text-slate-950 text-[10px] font-extrabold rounded-full" 
                          x-text="heldOrders.length"></span>
                </button>

                <!-- My Invoices Button -->
                <button type="button" 
                        @click="openInvoiceModal()"
                        class="px-3.5 py-2.5 bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs flex items-center gap-2 transition-all">
                    <i data-lucide="receipt" class="w-4 h-4 text-brand-400"></i>
                    <span>My Invoices</span>
                </button>
            </div>

        </div>

        <!-- Category Pills -->
        <div class="px-4 py-2.5 bg-slate-900/60 border-b border-slate-800/80 flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button @click="selectedCategory = 'all'" 
                    :class="selectedCategory === 'all' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                    class="px-4 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition-all">
                All Products
            </button>
            @foreach($categories as $cat)
                <button @click="selectedCategory = {{ $cat->id }}" 
                        :class="selectedCategory === {{ $cat->id }} ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'"
                        class="px-4 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap transition-all">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Product Grid List -->
        <div class="flex-1 p-4 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3.5 align-content-start">
            <template x-for="product in filteredProducts" :key="product.id">
                <div @click="addToCart(product)" 
                     class="bg-slate-900 border border-slate-800 hover:border-brand-500/50 hover:bg-slate-800/60 rounded-2xl p-3.5 flex flex-col justify-between cursor-pointer transition-all duration-200 group relative shadow-md">
                    
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="px-2 py-0.5 bg-slate-950 border border-slate-800 text-brand-400 font-mono text-[11px] font-bold rounded-md" x-text="product.item_code"></span>
                            
                            <!-- Stock Badge -->
                            <span :class="{
                                      'bg-emerald-500/10 text-emerald-400 border-emerald-500/30': product.stock_qty > 10,
                                      'bg-amber-500/10 text-amber-400 border-amber-500/30': product.stock_qty <= 10 && product.stock_qty > 0,
                                      'bg-rose-500/10 text-rose-400 border-rose-500/30': product.stock_qty <= 0
                                  }"
                                  class="px-2 py-0.5 border text-[10px] font-bold rounded-md flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full" :class="product.stock_qty > 0 ? 'bg-current' : 'bg-rose-500'"></span>
                                <span x-text="product.stock_qty > 0 ? product.stock_qty + ' in stock' : 'Out of stock'"></span>
                            </span>
                        </div>

                        <h3 class="font-semibold text-slate-100 text-xs sm:text-sm line-clamp-2 group-hover:text-white transition-colors" x-text="product.name"></h3>
                        <p class="text-[11px] text-slate-400 mt-1" x-text="product.category_name"></p>
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="font-extrabold text-white text-sm sm:text-base">
                            <span x-text="currencySymbol"></span> <span x-text="formatNumber(product.price)"></span>
                        </span>

                        <div class="w-8 h-8 rounded-lg bg-brand-600/20 text-brand-400 group-hover:bg-brand-600 group-hover:text-white flex items-center justify-center transition-all">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </div>
                    </div>

                </div>
            </template>

            <div x-show="filteredProducts.length === 0" class="col-span-full py-16 text-center text-slate-500">
                <i data-lucide="package-search" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p class="font-semibold text-sm">No products found matching filter.</p>
            </div>
        </div>

    </div>

    <!-- RIGHT SIDE: Shopping Cart Terminal -->
    <div class="w-full md:w-96 lg:w-[420px] bg-slate-900 flex flex-col md:h-full border-l border-slate-800/80 shadow-2xl">
        
        <!-- Cart Header -->
        <div class="p-4 bg-slate-950 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-5 h-5 text-brand-400"></i>
                <h2 class="font-bold text-white text-base">Current Bill</h2>
                <span class="px-2 py-0.5 bg-brand-600/20 text-brand-300 text-xs font-bold rounded-full" x-text="cartItemCount + ' items'"></span>
            </div>

            <button @click="clearCart()" 
                    x-show="cart.length > 0"
                    class="text-xs font-semibold text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1 transition-all">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                Clear Cart
            </button>
        </div>

        <!-- Cart Items List -->
        <div class="flex-1 p-3 overflow-y-auto space-y-2">
            <template x-for="(item, index) in cart" :key="item.id">
                <div class="p-3 bg-slate-950/80 border border-slate-800/80 rounded-xl flex items-center justify-between gap-2 hover:border-slate-700 transition-all">
                    
                    <!-- Item Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] text-brand-400 font-bold bg-brand-500/10 px-1.5 py-0.5 rounded" x-text="item.item_code"></span>
                        </div>
                        <h4 class="font-semibold text-xs text-white truncate mt-1" x-text="item.name"></h4>
                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">
                            <span x-text="currencySymbol"></span> <span x-text="formatNumber(item.price)"></span> × <span x-text="item.quantity"></span>
                        </div>
                    </div>

                    <!-- Quantity Controls -->
                    <div class="flex items-center gap-1 bg-slate-900 p-1 rounded-lg border border-slate-800">
                        <button @click="updateQty(index, item.quantity - 1)" 
                                class="w-6 h-6 flex items-center justify-center bg-slate-800 text-slate-300 hover:bg-slate-700 rounded-md font-bold text-xs">
                            -
                        </button>
                        <span class="w-7 text-center font-bold text-xs text-white" x-text="item.quantity"></span>
                        <button @click="updateQty(index, item.quantity + 1)" 
                                class="w-6 h-6 flex items-center justify-center bg-slate-800 text-slate-300 hover:bg-slate-700 rounded-md font-bold text-xs">
                            +
                        </button>
                    </div>

                    <!-- Item Subtotal -->
                    <div class="text-right min-w-[70px]">
                        <div class="font-extrabold text-xs text-white">
                            <span x-text="currencySymbol"></span> <span x-text="formatNumber(item.price * item.quantity)"></span>
                        </div>
                        <button @click="removeFromCart(index)" class="text-[10px] text-rose-400 hover:text-rose-300 transition-all">
                            Remove
                        </button>
                    </div>

                </div>
            </template>

            <div x-show="cart.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-500 text-center px-4">
                <div class="w-16 h-16 rounded-full bg-slate-950 border border-slate-800 flex items-center justify-center mb-3">
                    <i data-lucide="shopping-bag" class="w-8 h-8 opacity-40"></i>
                </div>
                <p class="font-semibold text-sm text-slate-400">Shopping Cart Empty</p>
                <p class="text-xs text-slate-500 mt-1">Scan an item code or tap a product to begin billing.</p>
            </div>
        </div>

        <!-- Cart Summary & Action Buttons -->
        <div class="p-4 bg-slate-950 border-t border-slate-800 space-y-3">
            
            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between text-slate-400 font-medium">
                    <span>Subtotal</span>
                    <span class="text-white font-semibold"><span x-text="currencySymbol"></span> <span x-text="formatNumber(cartTotal)"></span></span>
                </div>
                <div class="flex justify-between text-slate-400 font-medium">
                    <span>Tax / Vat (0%)</span>
                    <span class="text-white font-semibold"><span x-text="currencySymbol"></span> 0.00</span>
                </div>

                <!-- Discount Input -->
                <div x-show="cart.length > 0" class="pt-1.5 space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-medium">Discount</span>
                        <div class="flex bg-slate-900 border border-slate-800 rounded-lg p-0.5 text-[10px] font-bold">
                            <button @click="discountMode = 'percent'; discountInput = ''"
                                    :class="discountMode === 'percent' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                                    class="px-2 py-0.5 rounded-md transition-all">%</button>
                            <button @click="discountMode = 'amount'; discountInput = ''"
                                    :class="discountMode === 'amount' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                                    class="px-2 py-0.5 rounded-md transition-all">Amt</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" inputmode="decimal" x-model="discountInput"
                               :placeholder="discountMode === 'percent' ? 'Enter % discount' : 'Enter amount discount'"
                               class="flex-1 min-w-0 px-3 py-2 bg-slate-950 border border-slate-800 focus:border-brand-500 text-white font-bold text-xs rounded-lg focus:outline-none transition-all text-right">
                        <button @click="discountInput = ''" title="Clear discount"
                                class="px-2.5 py-2 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white font-bold text-[10px] rounded-lg transition-all">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <div x-show="discountMode === 'percent'" class="flex items-center gap-1.5">
                        <template x-for="d in [5, 10, 20]">
                            <button @click="discountMode = 'percent'; discountInput = String(d)"
                                    class="flex-1 py-1 bg-slate-900 border border-slate-800 hover:border-brand-500/50 text-slate-300 hover:text-white font-bold text-[10px] rounded-lg transition-all"
                                    x-text="d + '%'"></button>
                        </template>
                    </div>
                    <div x-show="discountMode === 'amount'" class="flex items-center gap-1.5">
                        <template x-for="d in [50, 100, 200]">
                            <button @click="discountMode = 'amount'; discountInput = String(d)"
                                    class="flex-1 py-1 bg-slate-900 border border-slate-800 hover:border-brand-500/50 text-slate-300 hover:text-white font-bold text-[10px] rounded-lg transition-all"
                                    x-text="currencySymbol + ' ' + d"></button>
                        </template>
                    </div>
                </div>

                <!-- Discount Applied Display -->
                <div class="flex justify-between text-slate-400 font-medium" x-show="discountAmount > 0">
                    <span>Discount <span x-text="discountPercentLabel"></span></span>
                    <span class="text-rose-400 font-semibold">- <span x-text="currencySymbol"></span> <span x-text="formatNumber(discountAmount)"></span></span>
                </div>

                <div class="flex justify-between text-base font-extrabold text-white pt-2 border-t border-slate-800">
                    <span class="text-brand-400">Grand Total</span>
                    <span class="text-emerald-400 text-lg"><span x-text="currencySymbol"></span> <span x-text="formatNumber(grandTotal)"></span></span>
                </div>
            </div>

            <!-- Action Buttons Grid: Hold Order & Checkout -->
            <div class="grid grid-cols-3 gap-2">
                
                <!-- HOLD ORDER BUTTON -->
                <button type="button" 
                        @click="holdCurrentCart()"
                        :disabled="cart.length === 0 || isHolding"
                        :class="cart.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-800 text-slate-500' : 'bg-amber-600/20 border border-amber-500/40 text-amber-300 hover:bg-amber-600/30'"
                        class="py-3 px-2 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="pause-circle" class="w-4 h-4 text-amber-400"></i>
                    <span x-text="isHolding ? 'Parking...' : 'Hold Sale'"></span>
                </button>

                <!-- CHECKOUT BUTTON -->
                <button @click="openPayModal()" 
                        :disabled="cart.length === 0"
                        :class="cart.length === 0 ? 'opacity-50 cursor-not-allowed bg-slate-800 text-slate-500' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white shadow-lg shadow-emerald-600/30'"
                        class="col-span-2 py-3 pl-4 pr-2.5 font-bold rounded-xl transition-all flex items-center justify-center gap-2 text-xs sm:text-sm">
                    <i data-lucide="credit-card" class="w-4 h-4 shrink-0"></i>
                    <span class="whitespace-nowrap overflow-hidden text-ellipsis min-w-0">Checkout (<span x-text="currencySymbol"></span> <span x-text="formatNumber(grandTotal)"></span>)</span>
                </button>

            </div>

        </div>

    </div>

    <!-- HELD ORDERS MODAL -->
    <div x-show="showHeldModal" x-cloak 
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div @click.away="showHeldModal = false" 
             class="bg-slate-900 border border-slate-800 w-full max-w-2xl rounded-3xl p-6 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center">
                        <i data-lucide="pause-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-white">Parked / Held Orders</h3>
                        <p class="text-xs text-slate-400">Select a held bill to recall into the active checkout screen.</p>
                    </div>
                </div>
                <button @click="showHeldModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- List of Held Orders -->
            <div class="max-h-96 overflow-y-auto space-y-3">
                <template x-for="held in heldOrders" :key="held.id">
                    <div class="p-4 bg-slate-950 border border-slate-800/80 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:border-amber-500/40 transition-all">
                        
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20" x-text="held.invoice_number"></span>
                                <span class="text-xs text-slate-400" x-text="new Date(held.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})"></span>
                            </div>
                            
                            <!-- Items preview string -->
                            <div class="text-xs text-slate-300 font-medium mt-1.5">
                                <span x-text="held.items.length + ' item(s): '"></span>
                                <span class="text-slate-400" x-text="held.items.map(i => i.product_name + ' (' + i.quantity + ')').join(', ')"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-center">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-500 uppercase font-semibold block">Total</span>
                                <span class="font-extrabold text-sm text-emerald-400"><span x-text="currencySymbol"></span> <span x-text="formatNumber(held.total_amount)"></span></span>
                            </div>

                            <button @click="recallOrder(held.id)" 
                                    class="px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl transition-all shadow-md shadow-brand-600/30 flex items-center gap-1">
                                <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                Recall Order
                            </button>

                            <button @click="discardHeldOrder(held.id)" 
                                    class="p-2 bg-slate-900 hover:bg-rose-600 text-slate-400 hover:text-white rounded-xl transition-all" 
                                    title="Discard Held Order">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>

                    </div>
                </template>

                <div x-show="heldOrders.length === 0" class="py-12 text-center text-slate-500">
                    <i data-lucide="pause-circle" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                    <p class="font-semibold text-sm text-slate-400">No currently held bills.</p>
                    <p class="text-xs text-slate-500 mt-1">Click "Hold Sale" on active bills to park them temporarily.</p>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button" @click="showHeldModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">
                    Close
                </button>
            </div>

        </div>
    </div>

    <!-- PAYMENT CHECKOUT MODAL -->
    <div x-show="showPayModal" x-cloak 
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div class="bg-slate-900 border border-slate-800 w-full max-w-xl rounded-3xl p-6 shadow-2xl space-y-5 relative max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-lg text-white">Payment Checkout</h3>
                </div>
                <button @click="showPayModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Amount Summary Card -->
            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800/80 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Due</span>
                    <div class="text-2xl font-extrabold text-white"><span x-text="currencySymbol"></span> <span x-text="formatNumber(grandTotal)"></span></div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Change Due</span>
                    <div class="text-2xl font-extrabold" :class="changeAmount >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                        <span x-text="currencySymbol"></span> <span x-text="formatNumber(changeAmount >= 0 ? changeAmount : 0)"></span>
                    </div>
                </div>
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Payment Method</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-brand-600 text-white border-brand-500 shadow-lg shadow-brand-600/30' : 'bg-slate-950 text-slate-300 border-slate-800 hover:bg-slate-800'"
                            class="py-2.5 px-4 border rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="coins" class="w-4 h-4"></i>
                        Cash Payment
                    </button>
                    <button type="button" 
                            @click="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'bg-brand-600 text-white border-brand-500 shadow-lg shadow-brand-600/30' : 'bg-slate-950 text-slate-300 border-slate-800 hover:bg-slate-800'"
                            class="py-2.5 px-4 border rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        Card Payment
                    </button>
                </div>
            </div>

            <!-- Paid Amount Input -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">
                    Amount Paid by Customer (<span x-text="currencySymbol"></span>)
                </label>
                <input type="text" 
                       inputmode="decimal" 
                       x-model="paidAmountStr" 
                       placeholder="Enter cash received..."
                       class="w-full px-4 py-3 bg-slate-950 border-2 border-slate-800 focus:border-brand-500 text-white font-extrabold text-xl rounded-xl focus:outline-none transition-all text-right">
            </div>

            <!-- Touch Numpad -->
            <div>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" @click="padInput('7')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">7</button>
                    <button type="button" @click="padInput('8')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">8</button>
                    <button type="button" @click="padInput('9')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">9</button>
                    <button type="button" @click="padInput('4')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">4</button>
                    <button type="button" @click="padInput('5')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">5</button>
                    <button type="button" @click="padInput('6')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">6</button>
                    <button type="button" @click="padInput('1')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">1</button>
                    <button type="button" @click="padInput('2')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">2</button>
                    <button type="button" @click="padInput('3')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">3</button>
                    <button type="button" @click="padInput('.')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">.</button>
                    <button type="button" @click="padInput('0')" class="py-3.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-white font-bold text-lg rounded-xl transition-all">0</button>
                    <button type="button" @click="padInput('backspace')" class="py-3.5 bg-rose-500/20 hover:bg-rose-500/30 active:scale-95 text-rose-300 font-bold text-lg rounded-xl transition-all flex items-center justify-center" title="Delete">
                        <i data-lucide="delete" class="w-5 h-5"></i>
                    </button>
                </div>
                <button type="button" @click="padInput('clear')" class="mt-2 w-full py-2.5 bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-300 font-bold text-xs rounded-xl transition-all">
                    Clear Amount
                </button>
            </div>

            <!-- Quick Cash Shortcuts -->
            <div>
                <span class="text-xs font-semibold text-slate-400 block mb-2">Quick Cash Amounts:</span>
                <div class="grid grid-cols-4 gap-2">
                    <button type="button" @click="paidAmountStr = String(grandTotal)" class="py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-white rounded-lg transition-all">Exact</button>
                    <button type="button" @click="paidAmountStr = '1000'" class="py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 rounded-lg transition-all">1,000</button>
                    <button type="button" @click="paidAmountStr = '5000'" class="py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 rounded-lg transition-all">5,000</button>
                    <button type="button" @click="paidAmountStr = '10000'" class="py-2 bg-slate-800 hover:bg-slate-700 text-xs font-bold text-slate-300 rounded-lg transition-all">10,000</button>
                </div>
            </div>

            <!-- Error Banner -->
            <div x-show="checkoutError" x-text="checkoutError" class="p-3 bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold rounded-xl"></div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 pt-3">
                <button type="button" @click="showPayModal = false" class="w-1/3 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs transition-all">
                    Cancel
                </button>
                <button type="button" 
                        @click="processCheckout()" 
                        :disabled="isSubmitting || paidAmount < grandTotal"
                        :class="(isSubmitting || paidAmount < grandTotal) ? 'opacity-50 cursor-not-allowed bg-slate-800 text-slate-500' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30'"
                        class="w-2/3 py-3 font-bold rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                    <span x-show="!isSubmitting" class="flex items-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Complete & Print Receipt
                    </span>
                    <span x-show="isSubmitting" class="flex items-center gap-2">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        Processing Transaction...
                    </span>
                </button>
            </div>

        </div>
    </div>

    <!-- MY INVOICES MODAL -->
    <div x-show="showInvoiceModal" x-cloak 
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div class="bg-slate-900 border border-slate-800 w-full max-w-2xl rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 text-brand-400 flex items-center justify-center">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-white">My Invoices</h3>
                        <p class="text-xs text-slate-400">Recent completed bills. Reprint the receipt or edit a bill when needed.</p>
                    </div>
                </div>
                <button @click="showInvoiceModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- List of Invoices -->
            <div class="max-h-96 overflow-y-auto space-y-3">
                <template x-for="inv in invoices" :key="inv.id">
                    <div class="p-4 bg-slate-950 border border-slate-800/80 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:border-brand-500/40 transition-all">
                        
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-brand-400 bg-brand-500/10 px-2 py-0.5 rounded border border-brand-500/20" x-text="inv.invoice_number"></span>
                                <span class="text-xs text-slate-400" x-text="new Date(inv.created_at).toLocaleString()"></span>
                            </div>
                            <div class="text-xs text-slate-400 font-medium mt-1.5">
                                <span x-text="inv.items.length + ' item(s) · ' + inv.payment_method.toUpperCase()"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-center">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-500 uppercase font-semibold block">Total</span>
                                <span class="font-extrabold text-sm text-emerald-400"><span x-text="currencySymbol"></span> <span x-text="formatNumber(inv.total_amount)"></span></span>
                            </div>

                            <button @click="printInvoice(inv)" 
                                    class="px-3 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs rounded-xl transition-all shadow-md shadow-brand-600/30 flex items-center gap-1">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                Reprint
                            </button>

                            <button @click="openEditInvoice(inv)" 
                                    class="px-3 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 font-bold text-xs rounded-xl transition-all border border-amber-500/30 flex items-center gap-1">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                Edit
                            </button>
                        </div>

                    </div>
                </template>

                <div x-show="invoices.length === 0" class="py-12 text-center text-slate-500">
                    <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                    <p class="font-semibold text-sm text-slate-400">No completed invoices yet.</p>
                    <p class="text-xs text-slate-500 mt-1">Billed transactions will appear here for reprinting and editing.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- EDIT INVOICE MODAL (CASHIER) -->
    <div x-show="showInvoiceEditModal" x-cloak 
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div class="bg-slate-900 border border-slate-800 w-full max-w-2xl rounded-3xl p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div>
                    <h3 class="font-bold text-lg text-white">Edit Invoice</h3>
                    <span class="font-mono text-xs text-brand-400 font-bold" x-text="invoiceEditNumber"></span>
                </div>
                <button @click="showInvoiceEditModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Editable Items -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Invoice Items</label>
                <div class="max-h-56 overflow-y-auto border border-slate-800 rounded-xl p-2 space-y-2">
                    <template x-for="(item, index) in invoiceEditItems" :key="item.product_id">
                        <div class="flex items-center justify-between gap-2 bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-white text-xs truncate" x-text="item.product_name"></div>
                                <div class="font-mono text-[10px] text-slate-500" x-text="item.item_code + ' · ' + currencySymbol + ' ' + formatNumber(item.unit_price)"></div>
                            </div>
                            <div class="flex items-center gap-1 bg-slate-900 p-0.5 rounded-lg border border-slate-800">
                                <button @click="changeInvoiceEditQty(index, -1)" class="w-6 h-6 flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-md font-bold text-xs">-</button>
                                <span class="w-7 text-center font-bold text-xs text-white" x-text="item.quantity"></span>
                                <button @click="changeInvoiceEditQty(index, 1)" class="w-6 h-6 flex items-center justify-center bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-md font-bold text-xs">+</button>
                            </div>
                            <div class="text-right w-20">
                                <div class="text-emerald-400 font-bold text-xs" x-text="currencySymbol + ' ' + formatNumber(item.quantity * item.unit_price)"></div>
                            </div>
                            <button @click="removeInvoiceEditItem(index)" class="p-1.5 bg-slate-900 hover:bg-rose-600 text-slate-400 hover:text-white rounded-lg transition-all" title="Remove item">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </template>
                    <div x-show="invoiceEditItems.length === 0" class="py-8 text-center text-slate-500 text-xs">
                        No items on this invoice. Add at least one product below.
                    </div>
                </div>

                <!-- Add Product Row -->
                <div class="mt-2 flex flex-col sm:flex-row gap-2">
                    <select x-model="invoiceAddProductId" class="flex-1 bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                        <option value="">Choose product to add...</option>
                        <template x-for="p in allProducts" :key="p.id">
                            <option :value="p.id" x-text="p.item_code + ' — ' + p.name + ' (' + currencySymbol + ' ' + formatNumber(p.price) + ')'"></option>
                        </template>
                    </select>
                    <input type="number" min="1" x-model.number="invoiceAddProductQty" placeholder="Qty"
                           class="w-20 bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                    <button @click="addInvoiceEditItem()" class="px-3 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Add Item
                    </button>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Payment Method</label>
                    <select x-model="invoiceEditPaymentMethod" class="w-full bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Amount Paid (<span x-text="currencySymbol"></span>)</label>
                    <input type="number" step="0.01" min="0" x-model.number="invoiceEditPaidAmount"
                           class="w-full bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Discount -->
            <div>
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Discount</label>
                <div class="flex items-center gap-2">
                    <div class="flex bg-slate-950 border border-slate-800 rounded-xl p-0.5 text-[10px] font-bold">
                        <button type="button" @click="invoiceEditDiscountMode = 'percent'"
                                :class="invoiceEditDiscountMode === 'percent' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">%</button>
                        <button type="button" @click="invoiceEditDiscountMode = 'amount'"
                                :class="invoiceEditDiscountMode === 'amount' ? 'bg-brand-600 text-white' : 'text-slate-400 hover:text-slate-200'"
                                class="px-2.5 py-1.5 rounded-lg transition-all">Amt</button>
                    </div>
                    <input type="number" step="0.01" min="0" x-model.number="invoiceEditDiscountInput"
                           :placeholder="invoiceEditDiscountMode === 'percent' ? 'Discount percentage' : 'Discount amount'"
                           class="flex-1 bg-slate-950 border border-slate-800 text-white text-xs font-semibold rounded-xl px-3 py-2 focus:outline-none focus:border-brand-500">
                </div>
            </div>

            <!-- Live Totals -->
            <div class="bg-slate-950 p-3 rounded-xl border border-slate-800/80 text-xs space-y-1 text-right">
                <div>Subtotal: <span class="text-slate-300"><span x-text="currencySymbol"></span> <span x-text="formatNumber(invoiceEditSubtotal)"></span></span></div>
                <div x-show="invoiceEditDiscountAmount > 0">Discount: <span class="text-rose-400">- <span x-text="currencySymbol"></span> <span x-text="formatNumber(invoiceEditDiscountAmount)"></span></span></div>
                <div>New Total: <strong class="text-emerald-400"><span x-text="currencySymbol"></span> <span x-text="formatNumber(invoiceEditTotal)"></span></strong></div>
                <div>Paid Amount: <span class="text-slate-300"><span x-text="currencySymbol"></span> <span x-text="formatNumber(invoiceEditPaidAmount || 0)"></span></span></div>
                <div>Change Returned: <span :class="invoiceEditChange >= 0 ? 'text-emerald-400' : 'text-rose-400'"><span x-text="currencySymbol"></span> <span x-text="formatNumber(Math.max(invoiceEditChange, 0))"></span></span></div>
            </div>

            <!-- Error Banner -->
            <div x-show="invoiceEditError" x-text="invoiceEditError" class="p-3 bg-rose-500/20 border border-rose-500/40 text-rose-300 text-xs font-semibold rounded-xl"></div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" @click="showInvoiceEditModal = false" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">
                    Cancel
                </button>
                <button type="button" @click="saveEditedInvoice()" :disabled="isSavingInvoice" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                    <span x-show="!isSavingInvoice" class="flex items-center gap-1">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        Save Changes
                    </span>
                    <span x-show="isSavingInvoice" class="flex items-center gap-1">
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
    function posSystem() {
        return {
            products: @json($products),
            allProducts: @json($allProducts),
            currencySymbol: "{{ $shopSettings['currency_symbol'] ?? 'LKR' }}",
            itemCodeInput: '',
            searchQuery: '',
            selectedCategory: 'all',
            cart: [],
            heldOrders: [],
            invoices: [],
            showPayModal: false,
            showHeldModal: false,
            showInvoiceModal: false,
            showInvoiceEditModal: false,
            invoiceEditId: null,
            invoiceEditNumber: '',
            invoiceEditItems: [],
            invoiceEditPaidAmount: 0,
            invoiceEditPaymentMethod: 'cash',
            invoiceEditDiscountMode: 'amount',
            invoiceEditDiscountInput: '0',
            invoiceAddProductId: '',
            invoiceAddProductQty: 1,
            isSavingInvoice: false,
            invoiceEditError: '',
            paidAmountStr: '0',
            paymentMethod: 'cash',
            discountMode: 'amount',
            discountInput: '',
            isSubmitting: false,
            isHolding: false,
            checkoutError: '',

            init() {
                this.fetchHeldOrders();
                this.$nextTick(() => {
                    if (this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                });
            },

            get filteredProducts() {
                return this.products.filter(p => {
                    const matchCategory = this.selectedCategory === 'all' || p.category_id === this.selectedCategory;
                    const matchQuery = !this.searchQuery || 
                                       p.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                       p.item_code.toLowerCase().includes(this.searchQuery.toLowerCase());
                    return matchCategory && matchQuery;
                });
            },

            get cartItemCount() {
                return this.cart.reduce((sum, i) => sum + i.quantity, 0);
            },

            get cartTotal() {
                return this.cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
            },

            get discountInputValue() {
                return parseFloat(this.discountInput) || 0;
            },

            get discountAmount() {
                if (this.discountInputValue <= 0) return 0;
                let amount;
                if (this.discountMode === 'percent') {
                    amount = (this.cartTotal * this.discountInputValue) / 100;
                } else {
                    amount = this.discountInputValue;
                }
                amount = Math.min(amount, this.cartTotal);
                return Math.round(amount * 100) / 100;
            },

            get discountPercentLabel() {
                if (this.discountMode === 'percent' && this.discountInputValue > 0) {
                    return '(' + this.discountInputValue + '%)';
                }
                return '';
            },

            get grandTotal() {
                return Math.max(0, Math.round((this.cartTotal - this.discountAmount) * 100) / 100);
            },

            get paidAmount() {
                return parseFloat(this.paidAmountStr) || 0;
            },

            get changeAmount() {
                return (this.paidAmount || 0) - this.grandTotal;
            },

            padInput(key) {
                let str = this.paidAmountStr;
                if (key === 'backspace') {
                    str = str.slice(0, -1);
                    if (str === '') str = '0';
                } else if (key === 'clear') {
                    str = '0';
                } else if (key === '.') {
                    if (!str.includes('.')) str += '.';
                } else {
                    if (str === '0') str = '';
                    str += key;
                }
                this.paidAmountStr = str;
            },

            get invoiceEditSubtotal() {
                return this.invoiceEditItems.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
            },

            get invoiceEditDiscountInputValue() {
                return parseFloat(this.invoiceEditDiscountInput) || 0;
            },

            get invoiceEditDiscountAmount() {
                if (this.invoiceEditDiscountInputValue <= 0) return 0;
                let amount;
                if (this.invoiceEditDiscountMode === 'percent') {
                    amount = (this.invoiceEditSubtotal * this.invoiceEditDiscountInputValue) / 100;
                } else {
                    amount = this.invoiceEditDiscountInputValue;
                }
                amount = Math.min(amount, this.invoiceEditSubtotal);
                return Math.round(amount * 100) / 100;
            },

            get invoiceEditTotal() {
                return Math.max(0, Math.round((this.invoiceEditSubtotal - this.invoiceEditDiscountAmount) * 100) / 100);
            },

            get invoiceEditChange() {
                return (parseFloat(this.invoiceEditPaidAmount) || 0) - this.invoiceEditTotal;
            },

            openInvoiceModal() {
                fetch("{{ route('pos.invoices') }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.invoices = data.invoices;
                            this.showInvoiceModal = true;
                        }
                    });
            },

            printInvoice(inv) {
                window.open("{{ url('orders') }}/" + inv.id + "/receipt", '_blank', 'width=400,height=600');
            },

            openEditInvoice(inv) {
                this.invoiceEditId = inv.id;
                this.invoiceEditNumber = inv.invoice_number;
                this.invoiceEditItems = inv.items.map(i => ({
                    product_id: i.product_id,
                    product_name: i.product_name,
                    item_code: i.item_code,
                    unit_price: parseFloat(i.unit_price),
                    quantity: i.quantity
                }));
                this.invoiceEditPaidAmount = parseFloat(inv.paid_amount);
                this.invoiceEditPaymentMethod = inv.payment_method;
                this.invoiceEditDiscountMode = inv.discount_percent && inv.discount_percent > 0 ? 'percent' : 'amount';
                this.invoiceEditDiscountInput = inv.discount_percent && inv.discount_percent > 0 ? String(inv.discount_percent) : String(inv.discount_amount || 0);
                this.invoiceAddProductId = '';
                this.invoiceAddProductQty = 1;
                this.invoiceEditError = '';
                this.showInvoiceEditModal = true;
            },

            changeInvoiceEditQty(index, delta) {
                const newQty = this.invoiceEditItems[index].quantity + delta;
                if (newQty < 1) return;
                this.invoiceEditItems[index].quantity = newQty;
            },

            removeInvoiceEditItem(index) {
                this.invoiceEditItems.splice(index, 1);
            },

            addInvoiceEditItem() {
                if (!this.invoiceAddProductId) return;
                const product = this.allProducts.find(p => p.id === Number(this.invoiceAddProductId));
                if (!product) return;

                const existing = this.invoiceEditItems.find(i => i.product_id === product.id);
                if (existing) {
                    existing.quantity += Math.max(1, this.invoiceAddProductQty || 1);
                } else {
                    this.invoiceEditItems.push({
                        product_id: product.id,
                        product_name: product.name,
                        item_code: product.item_code,
                        unit_price: parseFloat(product.price),
                        quantity: Math.max(1, this.invoiceAddProductQty || 1)
                    });
                }
                this.invoiceAddProductId = '';
                this.invoiceAddProductQty = 1;
            },

            saveEditedInvoice() {
                if (this.invoiceEditItems.length === 0) {
                    this.invoiceEditError = 'Invoice must contain at least one item.';
                    return;
                }
                if ((parseFloat(this.invoiceEditPaidAmount) || 0) < this.invoiceEditTotal) {
                    this.invoiceEditError = 'Paid amount cannot be less than the invoice total.';
                    return;
                }

                this.isSavingInvoice = true;
                this.invoiceEditError = '';

                fetch(`/pos/invoices/${this.invoiceEditId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: this.invoiceEditItems.map(i => ({ id: i.product_id, quantity: i.quantity })),
                        paid_amount: this.invoiceEditPaidAmount,
                        payment_method: this.invoiceEditPaymentMethod,
                        discount_percent: this.invoiceEditDiscountMode === 'percent' && this.invoiceEditDiscountInputValue > 0 ? this.invoiceEditDiscountInputValue : null,
                        discount_amount: this.invoiceEditDiscountAmount
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isSavingInvoice = false;
                    if (data.success) {
                        this.showInvoiceEditModal = false;
                        this.fetchMyInvoices();
                    } else {
                        this.invoiceEditError = data.message || 'Failed to update invoice.';
                    }
                })
                .catch(() => {
                    this.isSavingInvoice = false;
                    this.invoiceEditError = 'Network error updating invoice.';
                });
            },

            fetchMyInvoices() {
                fetch("{{ route('pos.invoices') }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.invoices = data.invoices;
                        }
                    });
            },

            formatNumber(num) {
                return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            fetchHeldOrders() {
                fetch("{{ route('pos.held-orders') }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.heldOrders = data.orders;
                        }
                    });
            },

            holdCurrentCart() {
                if (this.cart.length === 0) return;
                this.isHolding = true;

                fetch("{{ route('pos.hold') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: this.cart,
                        discount_percent: this.discountMode === 'percent' && this.discountInputValue > 0 ? this.discountInputValue : null,
                        discount_amount: this.discountAmount
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isHolding = false;
                    if (data.success) {
                        this.cart = [];
                        this.discountInput = '';
                        this.fetchHeldOrders();
                        alert('Current bill parked on hold.');
                    } else {
                        alert(data.message || 'Error holding order.');
                    }
                })
                .catch(err => {
                    this.isHolding = false;
                    alert('Network error holding order.');
                });
            },

            openHeldModal() {
                this.fetchHeldOrders();
                this.showHeldModal = true;
            },

            recallOrder(orderId) {
                if (this.cart.length > 0) {
                    if (!confirm('Active cart contains items. Replace active cart with held bill?')) {
                        return;
                    }
                }

                fetch(`/pos/recall/${orderId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.cart = data.items;
                        this.discountMode = data.discount_percent && data.discount_percent > 0 ? 'percent' : 'amount';
                        this.discountInput = data.discount_percent && data.discount_percent > 0 ? String(data.discount_percent) : (data.discount_amount > 0 ? String(data.discount_amount) : '');
                        this.showHeldModal = false;
                        this.fetchHeldOrders();
                    } else {
                        alert(data.message || 'Error recalling order.');
                    }
                });
            },

            discardHeldOrder(orderId) {
                if (!confirm('Permanently discard this held bill?')) return;

                fetch(`/pos/held-orders/${orderId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.fetchHeldOrders();
                    }
                });
            },

            scanItemCode() {
                const code = this.itemCodeInput.trim();
                if (!code) return;

                fetch("{{ route('pos.lookup') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ code: code })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.addToCart(data.product);
                        this.itemCodeInput = '';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => {
                    alert('Error scanning item code.');
                });
            },

            addToCart(product) {
                if (product.stock_qty <= 0) {
                    alert(`'${product.name}' is out of stock!`);
                    return;
                }

                const existingIndex = this.cart.findIndex(i => i.id === product.id);

                if (existingIndex > -1) {
                    const currentQty = this.cart[existingIndex].quantity;
                    if (currentQty + 1 > product.stock_qty) {
                        alert(`Cannot add more. Stock limit (${product.stock_qty}) reached for '${product.name}'.`);
                        return;
                    }
                    this.cart[existingIndex].quantity++;
                } else {
                    this.cart.push({
                        id: product.id,
                        name: product.name,
                        item_code: product.item_code,
                        price: product.price,
                        stock_qty: product.stock_qty,
                        quantity: 1
                    });
                }
            },

            updateQty(index, newQty) {
                if (newQty <= 0) {
                    this.removeFromCart(index);
                    return;
                }

                const product = this.cart[index];
                if (newQty > product.stock_qty) {
                    alert(`Maximum available stock for '${product.name}' is ${product.stock_qty}.`);
                    return;
                }

                this.cart[index].quantity = newQty;
            },

            removeFromCart(index) {
                this.cart.splice(index, 1);
            },

            clearCart() {
                if (confirm('Clear all items from current bill?')) {
                    this.cart = [];
                    this.discountInput = '';
                }
            },

            openPayModal() {
                if (this.cart.length === 0) return;
                this.paidAmountStr = String(this.grandTotal);
                this.checkoutError = '';
                this.showPayModal = true;
            },

            processCheckout() {
                if (this.paidAmount < this.grandTotal) {
                    this.checkoutError = 'Paid amount cannot be less than Grand Total.';
                    return;
                }

                this.isSubmitting = true;
                this.checkoutError = '';

                fetch("{{ route('pos.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: this.cart,
                        paid_amount: this.paidAmount,
                        payment_method: this.paymentMethod,
                        discount_percent: this.discountMode === 'percent' && this.discountInputValue > 0 ? this.discountInputValue : null,
                        discount_amount: this.discountAmount
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.isSubmitting = false;
                    if (data.success) {
                        // Open Thermal Receipt window
                        window.open(data.receipt_url, '_blank', 'width=400,height=600');

                        // Reset cart and update product stock locally
                        this.cart.forEach(item => {
                            const p = this.products.find(prod => prod.id === item.id);
                            if (p) p.stock_qty -= item.quantity;
                        });

                        this.cart = [];
                        this.discountInput = '';
                        this.showPayModal = false;
                        
                        // Refocus barcode input
                        this.$nextTick(() => {
                            if (this.$refs.barcodeInput) this.$refs.barcodeInput.focus();
                        });
                    } else {
                        this.checkoutError = data.message || 'Checkout failed.';
                    }
                })
                .catch(err => {
                    this.isSubmitting = false;
                    this.checkoutError = 'Network error processing checkout.';
                });
            }
        };
    }
</script>
@endpush
