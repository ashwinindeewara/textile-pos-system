@extends('layouts.app', ['title' => 'Vendor Dashboard - Textile POS'])

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Subscription Control</h1>
            <p class="text-sm text-slate-400 mt-1">Manage the store's subscription, payment due date, and system lock rules.</p>
        </div>

        <div class="flex items-center gap-3">
            @if ($isLocked)
                <span class="px-4 py-2.5 bg-rose-500/15 border border-rose-500/40 text-rose-300 font-bold text-xs rounded-xl flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    System Locked
                </span>
            @else
                <span class="px-4 py-2.5 bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 font-bold text-xs rounded-xl flex items-center gap-2">
                    <i data-lucide="lock-open" class="w-4 h-4"></i>
                    System Active
                </span>
            @endif
        </div>
    </div>

    <!-- Status Strip -->
    @if ($isLocked)
        <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-4 text-sm text-rose-200 flex items-center gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 shrink-0"></i>
            <span>
                @if ($lockReason === 'manual')
                    The system is <strong>manually locked</strong> by the vendor. Staff cannot use the POS until it is unlocked.
                @else
                    The subscription is <strong>overdue</strong> (due {{ optional($dueDate)->format('M d, Y') }}, lock date {{ optional($lockDate)->format('M d, Y') }}). The system auto-locked and staff have been blocked.
                @endif
            </span>
        </div>
    @endif

    <!-- Current Schedule -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Next Payment Due</span>
            <span class="text-xl font-extrabold text-white mt-1 block">{{ optional($dueDate)->format('M d, Y') ?? 'Not set' }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Grace Period</span>
            <span class="text-xl font-extrabold text-white mt-1 block">{{ $graceDays }} day{{ $graceDays === 1 ? '' : 's' }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Auto-Lock Date</span>
            <span class="text-xl font-extrabold text-amber-400 mt-1 block">{{ optional($lockDate)->format('M d, Y') ?? 'Not set' }}</span>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Until Lock</span>
            <span class="text-xl font-extrabold text-white mt-1 block">
                @if ($isLocked)
                    <span class="text-rose-400">Overdue</span>
                @elseif ($daysRemaining === null)
                    Not set
                @else
                    {{ $daysRemaining }} day{{ $daysRemaining === 1 ? '' : 's' }}
                @endif
            </span>
        </div>
    </div>

    <!-- Schedule Update -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center gap-2 mb-6 pb-3 border-b border-slate-800">
            <i data-lucide="calendar-days" class="w-5 h-5 text-emerald-400"></i>
            <h2 class="font-bold text-white text-base">Set Payment Schedule</h2>
        </div>

        <form method="POST" action="{{ route('superadmin.subscription.update') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Next Payment Due Date
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <input type="date"
                               name="due_date"
                               value="{{ old('due_date', optional($dueDate)->toDateString()) }}"
                               required
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold text-sm focus:outline-none focus:border-emerald-500 transition-all [color-scheme:dark]">
                    </div>
                    @error('due_date')
                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                        Auto-Lock After (Days Past Due)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i data-lucide="hourglass" class="w-4 h-4"></i>
                        </div>
                        <input type="number"
                               name="grace_days"
                               value="{{ old('grace_days', $graceDays) }}"
                               min="0"
                               max="365"
                               required
                               class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white font-bold text-sm focus:outline-none focus:border-emerald-500 transition-all">
                    </div>
                    @error('grace_days')
                        <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800/80 flex items-center justify-end">
                <button type="submit"
                        class="px-6 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Schedule
                </button>
            </div>
        </form>
    </div>

    <!-- Manual Lock Toggle -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-800">
            <i data-lucide="lock" class="w-5 h-5 text-rose-400"></i>
            <h2 class="font-bold text-white text-base">Manual System Lock</h2>
        </div>

        <p class="text-sm text-slate-400 mb-6">
            Instantly block every store staff account from using the POS (e.g. when a payment is missed). Super admin accounts
            always remain accessible.
        </p>

        <form method="POST" action="{{ route('superadmin.lock.toggle') }}" class="flex items-center gap-3">
            @csrf
            <input type="hidden" name="locked" value="{{ $isManuallyLocked ? '0' : '1' }}">

            @if ($isManuallyLocked)
                <button type="submit"
                        class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="lock-open" class="w-4 h-4"></i>
                    Unlock System Now
                </button>
            @else
                <button type="submit"
                        class="px-6 py-3.5 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl shadow-lg shadow-rose-600/30 transition-all flex items-center gap-2 text-sm">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    Lock System Now
                </button>
            @endif
            <span class="text-xs text-slate-500">{{ $staffCount }} staff account(s) will be affected.</span>
        </form>
    </div>

</div>
@endsection