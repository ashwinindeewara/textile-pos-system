<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->isSuperAdmin()
                ? redirect()->route('superadmin.dashboard')
                : redirect()->route('login');
        }

        return view('superadmin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->isSuperAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account is not authorized to access this portal.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('superadmin.dashboard'))
                ->with('success', 'Welcome back, Super Admin.');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        return view('superadmin.dashboard', [
            'dueDate' => SubscriptionService::dueDate(),
            'graceDays' => SubscriptionService::graceDays(),
            'lockDate' => SubscriptionService::lockDate(),
            'isLocked' => SubscriptionService::isLocked(),
            'isManuallyLocked' => SubscriptionService::isManuallyLocked(),
            'lockReason' => SubscriptionService::lockReason(),
            'daysRemaining' => SubscriptionService::daysRemaining(),
            'staffCount' => User::where('role', '!=', 'super_admin')->count(),
        ]);
    }

    public function updatePlan(Request $request)
    {
        $validated = $request->validate([
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:365'],
        ]);

        SubscriptionService::setDueDate($validated['due_date']);
        SubscriptionService::setGraceDays($validated['grace_days']);

        return redirect()->route('superadmin.dashboard')
            ->with('success', 'Subscription schedule updated.');
    }

    public function toggleLock(Request $request)
    {
        $validated = $request->validate([
            'locked' => ['required', 'boolean'],
        ]);

        SubscriptionService::setManuallyLocked((bool) $validated['locked']);

        return redirect()->route('superadmin.dashboard')
            ->with('success', $validated['locked'] ? 'System locked.' : 'System unlocked.');
    }

    public function locked()
    {
        return view('superadmin.locked', [
            'dueDate' => SubscriptionService::dueDate(),
            'lockDate' => SubscriptionService::lockDate(),
        ]);
    }
}
