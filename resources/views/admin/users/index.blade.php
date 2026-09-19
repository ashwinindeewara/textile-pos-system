@extends('layouts.app', ['title' => 'User Accounts - Textile POS'])

@section('content')
<div x-data="userManagement()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Validation Errors -->
    @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-4 text-rose-300 text-sm space-y-1">
            <div class="font-semibold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                Please fix the following
            </div>
            @foreach($errors->all() as $error)
                <p class="text-xs pl-6">• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">User / Staff Accounts</h1>
            <p class="text-sm text-slate-400 mt-1">Create and manage administrator and cashier accounts for the POS system.</p>
        </div>

        <button @click="openCreateModal()"
                class="px-4 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-2 self-start">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Add New Account
        </button>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
        <form method="GET" action="{{ route('admin.users.index') }}" class="w-full flex flex-col sm:flex-row gap-3">

            <!-- Role Filter -->
            <select name="role" onchange="this.form.submit()" class="bg-slate-950 border border-slate-800 text-slate-200 text-xs font-semibold rounded-xl px-3 py-2.5 focus:outline-none focus:border-brand-500">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admins</option>
                <option value="cashier" {{ request('role') == 'cashier' ? 'selected' : '' }}>Cashiers</option>
            </select>

            <!-- Search Field -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by name or email..."
                       class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 text-white rounded-xl text-xs font-medium focus:outline-none focus:border-brand-500">
            </div>

            <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-xl transition-all">
                Filter
            </button>
            @if(request()->hasAny(['role', 'search']))
                <a href="{{ route('admin.users.index') }}" class="px-3 py-2.5 bg-slate-950 text-slate-400 hover:text-white text-xs font-bold rounded-xl flex items-center gap-1">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-950 text-slate-400 font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-4">Name</th>
                        <th class="p-4">Email Address</th>
                        <th class="p-4 text-center">Role</th>
                        <th class="p-4">Created</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr {{ $user->isAdmin() ? 'from-brand-600 to-indigo-500' : 'from-emerald-600 to-teal-500' }} flex items-center justify-center text-white font-extrabold text-xs uppercase shadow-md">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white flex items-center gap-2">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="px-2 py-0.5 bg-brand-500/15 text-brand-300 border border-brand-500/30 text-[10px] font-bold rounded-md">You</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500">ID: #{{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-xs font-medium text-slate-300">
                                {{ $user->email }}
                            </td>
                            <td class="p-4 text-center">
                                @if($user->isAdmin())
                                    <span class="px-2.5 py-1 bg-indigo-500/10 text-indigo-300 border border-indigo-500/30 text-[11px] font-bold rounded-md uppercase">
                                        Administrator
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 text-[11px] font-bold rounded-md uppercase">
                                        Cashier
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-xs text-slate-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal({{ json_encode($user) }})"
                                            class="p-2 bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white rounded-lg transition-all"
                                            title="Edit Account">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" onsubmit="return confirm('Delete this account permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white rounded-lg transition-all" title="Delete Account">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 text-sm">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                No user accounts found. Click "Add New Account" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- CREATE / EDIT USER MODAL -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-slate-900 border border-slate-800 w-full max-w-lg rounded-3xl p-6 shadow-2xl space-y-4">

            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-lg text-white" x-text="isEdit ? 'Edit User Account' : 'Add New User Account'"></h3>
                <button @click="showModal = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="formUrl" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Full Name</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Kasun Perera"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" x-model="form.email" required placeholder="user@textilepos.com"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">User Role</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button"
                                @click="form.role = 'cashier'"
                                :class="form.role === 'cashier' ? 'bg-emerald-600 text-white border-emerald-500 shadow-lg shadow-emerald-600/30' : 'bg-slate-950 text-slate-300 border-slate-800 hover:bg-slate-800'"
                                class="py-2.5 px-4 border rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                            Cashier
                        </button>
                        <button type="button"
                                @click="form.role = 'admin'"
                                :class="form.role === 'admin' ? 'bg-indigo-600 text-white border-indigo-500 shadow-lg shadow-indigo-600/30' : 'bg-slate-950 text-slate-300 border-slate-800 hover:bg-slate-800'"
                                class="py-2.5 px-4 border rounded-xl font-bold text-xs flex items-center justify-center gap-2 transition-all">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            Admin
                        </button>
                    </div>
                    <input type="hidden" name="role" :value="form.role">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">
                            <span x-text="isEdit ? 'New Password (optional)' : 'Password'"></span>
                        </label>
                        <input type="password" name="password" x-model="form.password" :required="!isEdit" minlength="6" placeholder="Min. 6 characters"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" x-model="form.password_confirmation" :required="!isEdit" minlength="6" placeholder="Repeat password"
                               class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" @click="showModal = false" class="w-1/3 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-600/30">
                        <span x-text="isEdit ? 'Update Account' : 'Create Account'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function userManagement() {
        return {
            showModal: false,
            isEdit: false,
            formUrl: '',
            form: {
                id: null,
                name: '',
                email: '',
                role: 'cashier',
                password: '',
                password_confirmation: ''
            },

            openCreateModal() {
                this.isEdit = false;
                this.formUrl = "{{ route('admin.users.store') }}";
                this.form = { id: null, name: '', email: '', role: 'cashier', password: '', password_confirmation: '' };
                this.showModal = true;
            },

            openEditModal(user) {
                this.isEdit = true;
                this.formUrl = `/admin/users/${user.id}`;
                this.form = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    role: user.role,
                    password: '',
                    password_confirmation: ''
                };
                this.showModal = true;
            }
        };
    }
</script>
@endpush