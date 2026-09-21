<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role') && in_array($request->role, ['admin', 'cashier'])) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->where('role', '!=', 'super_admin')->orderBy('role')->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,cashier',
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User account created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,cashier',
        ]);

        // Guard: a user cannot change their own role away from the last admin.
        if ($user->id === Auth::id()
            && $user->isAdmin()
            && $validated['role'] !== 'admin'
            && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You are the only administrator. Promote another account first before changing your role.');
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User account updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vendor accounts cannot be managed from the store panel.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete the last administrator account.');
        }

        if ($user->orders()->exists()) {
            return redirect()->route('admin.users.index')
                ->with('error', "Cannot delete '{$user->name}': they have sales history linked to this account.");
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User account deleted successfully.');
    }
}
