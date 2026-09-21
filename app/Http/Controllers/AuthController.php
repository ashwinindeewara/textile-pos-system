<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            return $user->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('pos.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('superadmin.dashboard'))
                    ->with('success', 'Welcome back, Super Admin.');
            }

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, Admin!');
            }

            return redirect()->intended(route('pos.index'))
                ->with('success', 'POS Terminal ready.');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
