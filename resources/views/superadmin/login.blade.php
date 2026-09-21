@extends('layouts.app', ['title' => 'Vendor Portal - Textile POS'])

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black">
    <div class="w-full max-w-md">

        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-500 to-cyan-500 mx-auto flex items-center justify-center shadow-xl shadow-emerald-500/25 mb-4 border border-white/10">
                <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">VENDOR PORTAL</h1>
            <p class="text-sm text-slate-400 mt-1 font-medium">Subscription & System Control</p>
        </div>

        <!-- Login Form Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 p-8 rounded-3xl shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>

            <h2 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                <i data-lucide="lock" class="w-5 h-5 text-emerald-500"></i>
                Authorized Access Only
            </h2>

            @if ($errors->any())
                <div class="mb-6 bg-rose-500/10 border border-rose-500/30 rounded-xl p-4 text-rose-300 text-sm">
                    <div class="font-semibold flex items-center gap-2 mb-1">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        Access Denied
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('superadmin.login.submit') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Vendor Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               placeholder="vender@provider.com"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all text-sm font-medium">
                    </div>
                </div>

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
                               required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-3 bg-slate-950/80 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all text-sm font-medium">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all transform active:scale-[0.99] flex items-center justify-center gap-2 text-sm">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    Enter Vendor Portal
                </button>
            </form>

        </div>

    </div>
</div>
@endsection