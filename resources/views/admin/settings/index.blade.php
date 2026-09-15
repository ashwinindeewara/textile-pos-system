@extends('layouts.app', ['title' => 'Shop Settings - Textile POS'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-extrabold text-white tracking-tight">Shop & Receipt Settings</h1>
        <p class="text-sm text-slate-400 mt-1">Configure global store details, currency symbol, logo, phone number, and thermal receipt footer message.</p>
    </div>

    <!-- Settings Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Shop Name & Currency Symbol Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                
                <!-- Shop Name -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Shop / Store Name
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="store" class="w-4 h-4"></i>
                        </div>
                        <input type="text" 
                               name="shop_name" 
                               value="{{ old('shop_name', $settings['shop_name']) }}" 
                               required 
                               placeholder="e.g. SILK & DENIM"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                </div>

                <!-- Currency Symbol -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Currency Symbol
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="coins" class="w-4 h-4"></i>
                        </div>
                        <input type="text" 
                               name="currency_symbol" 
                               value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'LKR') }}" 
                               required 
                               placeholder="e.g. LKR, $, €, £, ₹, AED"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                </div>

            </div>

            <!-- Address & Phone Number Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                
                <!-- Phone Number -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Store Phone Number
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <input type="text" 
                               name="phone_number" 
                               value="{{ old('phone_number', $settings['phone_number']) }}" 
                               required 
                               placeholder="e.g. +94 11 234 5678"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white font-medium text-sm focus:outline-none focus:border-brand-500 transition-all">
                    </div>
                </div>

                <!-- Logo Upload -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Shop Logo Image (Optional)
                    </label>
                    <input type="file" 
                           name="logo" 
                           accept="image/*"
                           class="w-full text-xs text-slate-400 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-600 file:text-white hover:file:bg-brand-500 cursor-pointer">
                    @if(!empty($settings['logo_path']))
                        <div class="mt-2 flex items-center gap-2">
                            <img src="{{ asset($settings['logo_path']) }}" alt="Shop Logo" class="w-8 h-8 rounded-lg object-contain bg-white p-1">
                            <span class="text-[11px] text-emerald-400 font-medium">Custom logo uploaded</span>
                        </div>
                    @endif
                </div>

            </div>

            <!-- Shop Address -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                    Store Address (Appears on Bills)
                </label>
                <textarea name="shop_address" 
                          rows="2" 
                          required 
                          placeholder="e.g. 123 Fashion Street, Colombo 03"
                          class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm font-medium focus:outline-none focus:border-brand-500 transition-all">{{ old('shop_address', $settings['shop_address']) }}</textarea>
            </div>

            <!-- Thermal Receipt Footer Message -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                    Thermal Bill Footer Message
                </label>
                <textarea name="receipt_footer" 
                          rows="2" 
                          required 
                          placeholder="e.g. Exchanges allowed within 7 days with bill. Thank you!"
                          class="w-full p-3.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm font-medium focus:outline-none focus:border-brand-500 transition-all">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-end">
                <button type="submit" 
                        class="px-6 py-3.5 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Shop Settings
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
