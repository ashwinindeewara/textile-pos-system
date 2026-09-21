<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>System Locked - Textile POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full font-sans antialiased bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-900 via-slate-950 to-black flex items-center justify-center p-4">

    <div class="w-full max-w-md text-center">

        <div class="w-20 h-20 rounded-3xl bg-rose-500/10 border border-rose-500/30 mx-auto flex items-center justify-center shadow-2xl shadow-rose-500/10 mb-6">
            <i data-lucide="lock" class="w-10 h-10 text-rose-400"></i>
        </div>

        <h1 class="text-3xl font-extrabold tracking-tight text-white mb-3">System Locked</h1>
        <p class="text-sm text-slate-400 leading-relaxed mb-8">
            This system has been locked because the subscription fee is overdue.
            Please contact your service provider to renew your subscription and restore access.
        </p>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 mb-8 flex items-center justify-center gap-6 text-sm">
            <div class="text-center">
                <span class="block text-[11px] uppercase tracking-wider text-slate-500 font-semibold mb-1">Last Due</span>
                <span class="font-bold text-white">{{ $dueDate ? $dueDate->format('M d, Y') : '—' }}</span>
            </div>
            <div class="w-px h-8 bg-slate-800"></div>
            <div class="text-center">
                <span class="block text-[11px] uppercase tracking-wider text-slate-500 font-semibold mb-1">Locked Since</span>
                <span class="font-bold text-rose-400">{{ $lockDate ? $lockDate->format('M d, Y') : 'Now' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl border border-slate-700 transition-all text-sm">
                <i data-lucide="log-out" class="w-4 h-4 inline mr-1"></i>
                Sign Out
            </button>
        </form>

    </div>

    <script>
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    </script>
</body>
</html>