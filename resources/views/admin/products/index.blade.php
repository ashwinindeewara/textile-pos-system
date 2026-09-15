@extends('layouts.app', ['title' => 'Inventory Management - Textile POS'])

@section('content')
<div x-data="productManagement()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Inventory Products</h1>
            <p class="text-sm text-slate-400 mt-1">Manage textile item codes, prices, categories, and stock quantities.</p>
        </div>

        <button @click="openCreateModal()" 
                class="px-4 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-2 self-start">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Add New Product
        </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
        <form method="GET" action="{{ route('admin.products.index') }}" class="w-full flex flex-col sm:flex-row gap-3">
            
            <!-- Category Filter -->
            <select name="category" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 text-slate-200 text-xs font-semibold rounded-xl px-3 py-2.5 focus:outline-none focus:border-brand-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>

            <!-- Search Field -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by product name or item code (e.g. DNM-001)..." 
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 text-white rounded-xl text-xs font-medium focus:outline-none focus:border-brand-500">
            </div>

            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition-all">
                Filter
            </button>
            @if(request()->hasAny(['category', 'search']))
                <a href="{{ route('admin.products.index') }}" class="px-3 py-2.5 bg-slate-950 text-slate-400 hover:text-white text-xs font-bold rounded-xl flex items-center gap-1">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-4">Item Code</th>
                        <th class="p-4">Product Name</th>
                        <th class="p-4">Category</th>
                        <th class="p-4 text-right">Price ({{ $shopSettings['currency_symbol'] ?? 'LKR' }})</th>
                        <th class="p-4 text-center">Stock Qty</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            
                            <!-- Code -->
                            <td class="p-4">
                                <span class="font-mono text-xs font-bold text-brand-400 bg-brand-500/10 border border-brand-500/20 px-2.5 py-1 rounded-lg">
                                    {{ $product->item_code }}
                                </span>
                            </td>

                            <!-- Name -->
                            <td class="p-4 font-semibold text-white">
                                {{ $product->name }}
                            </td>

                            <!-- Category -->
                            <td class="p-4 text-xs font-medium text-slate-400">
                                {{ optional($product->category)->name ?? 'Uncategorized' }}
                            </td>

                            <!-- Price -->
                            <td class="p-4 text-right font-extrabold text-white">
                                {{ $shopSettings['currency_symbol'] ?? 'LKR' }} {{ number_format($product->price, 2) }}
                            </td>

                            <!-- Stock Qty -->
                            <td class="p-4 text-center font-extrabold text-white text-base">
                                {{ number_format($product->stock_qty) }}
                            </td>

                            <!-- Stock Status -->
                            <td class="p-4 text-center">
                                @if($product->stock_qty <= 0)
                                    <span class="px-2.5 py-1 bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[11px] font-bold rounded-md">
                                        Out of stock
                                    </span>
                                @elseif($product->stock_qty <= 5)
                                    <span class="px-2.5 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-bold rounded-md">
                                        Low stock ({{ $product->stock_qty }})
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-bold rounded-md">
                                        In Stock
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal({{ json_encode($product) }})" 
                                            class="p-2 bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white rounded-lg transition-all" 
                                            title="Edit Product">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    
                                    <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" onsubmit="return confirm('Delete this product permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white rounded-lg transition-all" title="Delete Product">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 text-sm">
                                <i data-lucide="package-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                No inventory products found. Click "Add New Product" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- CREATE / EDIT PRODUCT MODAL -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-lg text-white" x-text="isEdit ? 'Edit Product' : 'Add New Textile Product'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="formUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Item Code / Barcode</label>
                    <input type="text" name="item_code" x-model="form.item_code" required placeholder="e.g. DNM-005"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm font-mono font-bold focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Product Name</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Stretch Slim Denim Jeans"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Category</label>
                        <select name="category_id" x-model="form.category_id" required class="w-full px-3 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Unit Price ({{ $shopSettings['currency_symbol'] ?? 'LKR' }})</label>
                        <input type="number" step="0.01" name="price" x-model="form.price" required placeholder="0.00"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm font-bold focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Stock Quantity</label>
                    <input type="number" name="stock_qty" x-model="form.stock_qty" required placeholder="0"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm font-bold focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" @click="showModal = false" class="w-1/3 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-600/30">
                        <span x-text="isEdit ? 'Update Product' : 'Save Product'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function productManagement() {
        return {
            showModal: false,
            isEdit: false,
            formUrl: '',
            form: {
                id: null,
                item_code: '',
                name: '',
                category_id: '',
                price: '',
                stock_qty: 0
            },

            openCreateModal() {
                this.isEdit = false;
                this.formUrl = "{{ route('admin.products.store') }}";
                this.form = { id: null, item_code: '', name: '', category_id: '', price: '', stock_qty: 0 };
                this.showModal = true;
            },

            openEditModal(product) {
                this.isEdit = true;
                this.formUrl = `/admin/products/${product.id}`;
                this.form = {
                    id: product.id,
                    item_code: product.item_code,
                    name: product.name,
                    category_id: product.category_id,
                    price: product.price,
                    stock_qty: product.stock_qty
                };
                this.showModal = true;
            }
        };
    }
</script>
@endpush
