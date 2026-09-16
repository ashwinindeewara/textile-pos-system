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
    <header class="bg-slate-950 border-b border-slate-800/80 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    @if(!empty($shopSettings['logo_path']))
                        <img src="{{ asset($shopSettings['logo_path']) }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain bg-white p-1 shadow-lg shadow-brand-500/20">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 flex items-center justify-center shadow-lg shadow-brand-500/20">
                            <i data-lucide="scissors" class="w-5 h-5 text-white"></i>
                        </div>
                    @endif
                    <div>
                        <span class="font-bold text-lg text-white tracking-wide uppercase">{{ $shopSettings['shop_name'] ?? 'SILK & DENIM' }}</span>
                        <span class="text-xs block text-slate-400 font-medium -mt-1">Textile POS & Inventory</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-1">
                    @if(auth()->user()->isCashier() || auth()->user()->isAdmin())
                        <a href="{{ route('pos.index') }}" 
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('pos.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            POS Counter
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.products.index') }}" 
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('admin.products.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="package" class="w-4 h-4"></i>
                            Products
                        </a>
                        <a href="{{ route('admin.categories.index') }}" 
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="tags" class="w-4 h-4"></i>
                            Categories
                        </a>
                        <a href="{{ route('admin.sales.index') }}" 
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('admin.sales.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                            Sales History
                        </a>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                            Shop Settings
                        </a>
                    @endif
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
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
