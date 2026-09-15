@extends('layouts.app', ['title' => 'Login - Textile POS'])

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black">
    <div class="w-full max-w-md" x-data="{ email: '', password: '' }">
        
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-purple-500 mx-auto flex items-center justify-center shadow-xl shadow-brand-500/25 mb-4 border border-white/10">
                <i data-lucide="scissors" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">SILK & DENIM POS</h1>
            <p class="text-sm text-slate-400 mt-1 font-medium">Textile Store Billing & Inventory Management</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 p-8 rounded-3xl shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-brand-500/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>

            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <i data-lucide="lock" class="w-5 h-5 text-brand-500"></i>
                Sign In to Account
            </h2>

            @if ($errors->any())
                <div class="mb-6 bg-rose-500/10 border border-rose-500/30 rounded-xl p-4 text-rose-300 text-sm">
                    <div class="font-semibold flex items-center gap-2 mb-1">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        Authentication Failed
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
                @csrf

                <!-- Email Field -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" 
                               name="email" 
                               x-model="email"
                               required 
                               autofocus
                               placeholder="user@textilepos.com"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all text-sm font-medium">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                        </div>
                        <input type="password" 
                               name="password" 
                               x-model="password"
                               required 
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all text-sm font-medium">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400 hover:text-slate-300">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-brand-600 focus:ring-brand-500">
                        <span>Remember credentials</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-brand-600/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    Login to System
                </button>
            </form>

            <!-- Quick Demo Credentials helper -->
            <div class="mt-8 pt-6 border-t border-slate-800/80">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider text-center mb-3">Quick Demo Login</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" 
                            @click="email = 'admin@textilepos.com'; password = 'password'"
                            class="px-3 py-2 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 rounded-xl text-xs font-medium transition-all text-left flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-400"></i>
                        <span><strong>Admin</strong> Account</span>
                    </button>
                    <button type="button" 
                            @click="email = 'cashier@textilepos.com'; password = 'password'"
                            class="px-3 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 rounded-xl text-xs font-medium transition-all text-left flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span><strong>Cashier</strong> Account</span>
                    </button>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
