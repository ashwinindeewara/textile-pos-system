<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Textile POS System' }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="h-full font-sans antialiased selection:bg-brand-500 selection:text-white flex flex-col">

    <!-- Flash Notifications -->
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
             class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-2xl border border-emerald-400/30 transition-all">
            <i data-lucide="check-circle" class="w-5 h-5"></i>
            <span class="font-medium text-sm">{{ session('success') }}</span>
            <button @click="show = false" class="ml-2 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
             class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-rose-600 text-white px-5 py-3 rounded-xl shadow-2xl border border-rose-400/30 transition-all">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            <span class="font-medium text-sm">{{ session('error') }}</span>
            <button @click="show = false" class="ml-2 hover:opacity-75"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    @endif

    @auth
    <!-- Top Navigation Header -->
    <header class="bg-slate-950 border-b border-slate-800/80 sticky top-0 z-40" x-data="{ navOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 h-16">

                <!-- Brand / Logo -->
                <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.dashboard') : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('pos.index')) }}" class="flex items-center gap-2.5 min-w-0 shrink-0">
                    @if(!empty($shopSettings['logo_path']))
                        <img src="{{ asset($shopSettings['logo_path']) }}" alt="Logo" class="w-9 h-9 rounded-xl object-contain bg-white p-1 shadow-lg shadow-brand-500/20 shrink-0">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-brand-500/20 shrink-0">
                            <i data-lucide="scissors" class="w-5 h-5 text-white"></i>
                        </div>
                    @endif
                    <div class="leading-tight min-w-0 hidden sm:block">
                        <span class="font-bold text-base text-white tracking-wide uppercase truncate block">{{ $shopSettings['shop_name'] ?? 'SILK & DENIM' }}</span>
                        <span class="text-[11px] block text-slate-400 font-medium truncate">Textile POS & Inventory</span>
                    </div>
                </a>

                <!-- Navigation Links (desktop) -->
                <nav class="hidden xl:flex items-center gap-0.5 overflow-x-auto">
                    @if(auth()->user()->isCashier() || auth()->user()->isAdmin())
                        <a href="{{ route('pos.index') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('pos.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            POS Counter
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.products.index') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.products.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="package" class="w-4 h-4"></i>
                            Products
                        </a>
                        <a href="{{ route('admin.categories.index') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="tags" class="w-4 h-4"></i>
                            Categories
                        </a>
                        <a href="{{ route('admin.sales.index') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.sales.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            Sales
                        </a>
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.users.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Users
                        </a>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                            Settings
                        </a>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('superadmin.dashboard') }}" 
                           class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-all {{ request()->routeIs('superadmin.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="shield" class="w-4 h-4"></i>
                            Subscription
                        </a>
                    @endif
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-3 shrink-0">
                    <div class="text-right hidden lg:block">
                        <div class="text-sm font-semibold text-white leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-indigo-400 capitalize font-medium flex items-center justify-end gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            {{ auth()->user()->role }}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                title="Logout"
                                class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-rose-400 hover:border-rose-500/30 hover:bg-rose-500/10 transition-all">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>

                    <!-- Mobile Menu Toggle -->
                    <button type="button"
                            @click="navOpen = !navOpen"
                            title="Menu"
                            class="xl:hidden p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition-all">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile / Tablet Navigation Dropdown -->
        <div x-show="navOpen" x-cloak class="xl:hidden bg-slate-950 border-t border-slate-800 shadow-2xl">
            <div class="max-w-7xl mx-auto px-4 py-3 space-y-1">
                @if(auth()->user()->isCashier() || auth()->user()->isAdmin())
                    <a href="{{ route('pos.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('pos.*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        POS Counter
                    </a>
                @endif

                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.products.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.products.*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="package" class="w-4 h-4"></i>
                        Products
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="tags" class="w-4 h-4"></i>
                        Categories
                    </a>
                    <a href="{{ route('admin.sales.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.sales.*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                        Sales History
                    </a>
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.users.*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        User Accounts
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-brand-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        Shop Settings
                    </a>
                @endif

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('superadmin.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('superadmin.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                        Subscription
                    </a>
                @endif

                <div class="flex items-center justify-between gap-3 px-3 py-2.5 mt-1 border-t border-slate-800 lg:hidden">
                    <div class="text-sm font-semibold text-white leading-tight">{{ auth()->user()->name }}</div>
                    <span class="text-[11px] text-indigo-400 uppercase font-bold px-2 py-1 bg-indigo-500/10 border border-indigo-500/30 rounded-md">
                        {{ auth()->user()->role }}
                    </span>
                </div>
            </div>
        </div>
    </header>
    @endauth

    <!-- Main Content Area -->
    <main class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <script>
    function renderLucideIcons() {
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
            // Lucide leaves the data-lucide marker on the generated <svg>, so
            // createIcons() would re-process them on every call (and this can
            // loop forever). Strip the marker so each icon renders only once.
            document.querySelectorAll('[data-lucide]').forEach(function (el) {
                if (el.tagName && el.tagName.toLowerCase() === 'svg') {
                    el.removeAttribute('data-lucide');
                }
            });
        }
    }

    renderLucideIcons();

    // Re-render icons whenever Alpine injects new DOM (x-for / x-if lists,
    // modals, dynamically shown rows etc.) so every button icon stays visible.
    if (window.MutationObserver) {
        const iconObserver = new MutationObserver(function (mutations) {
            let needsRender = false;

            for (let m = 0; m < mutations.length; m++) {
                const added = mutations[m].addedNodes;
                for (let i = 0; i < added.length; i++) {
                    const node = added[i];
                    if (node.nodeType !== 1) continue;
                    // In HTML documents SVG elements report 'svg' (lowercase).
                    if (node.tagName && node.tagName.toLowerCase() === 'svg') continue;
                    if (node.hasAttribute && node.hasAttribute('data-lucide')) {
                        needsRender = true;
                        break;
                    }
                    if (node.querySelector && node.querySelector('[data-lucide]')) {
                        needsRender = true;
                        break;
                    }
                }
                if (needsRender) break;
            }

            if (needsRender) renderLucideIcons();
        });

        iconObserver.observe(document.body, { childList: true, subtree: true });
    }
</script>
    @stack('scripts')
</body>
</html>
