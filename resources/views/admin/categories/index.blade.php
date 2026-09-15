@extends('layouts.app', ['title' => 'Category Management - Textile POS'])

@section('content')
<div x-data="categoryManagement()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Product Categories</h1>
            <p class="text-sm text-slate-400 mt-1">Organize textile inventory into categories (e.g. Denims, Frocks, Sarees).</p>
        </div>

        <button @click="openCreateModal()" 
                class="px-4 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-2 self-start">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            Add New Category
        </button>
    </div>

    <!-- Category Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($categories as $category)
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl flex items-center justify-between hover:border-slate-700 transition-all">
                
                <div>
                    <h3 class="font-bold text-white text-base">{{ $category->name }}</h3>
                    <span class="text-xs text-slate-400 font-mono mt-0.5 block">slug: {{ $category->slug }}</span>
                    <span class="mt-2 inline-block px-2.5 py-0.5 bg-brand-500/10 text-brand-300 font-bold text-xs rounded-md">
                        {{ $category->products_count }} products
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button @click="openEditModal({{ json_encode($category) }})" 
                            class="p-2 bg-slate-800 hover:bg-brand-600 text-slate-300 hover:text-white rounded-lg transition-all"
                            title="Edit Category">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>

                    <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" onsubmit="return confirm('Delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="p-2 bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white rounded-lg transition-all"
                                title="Delete Category">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>

            </div>
        @endforeach
    </div>

    <!-- MODAL -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-lg text-white" x-text="isEdit ? 'Edit Category' : 'Add New Category'"></h3>
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
                    <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Category Name</label>
                    <input type="text" name="name" x-model="form.name" required placeholder="e.g. Sarees & Ethnic"
                           class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500">
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" @click="showModal = false" class="w-1/3 py-2.5 bg-slate-800 text-slate-300 font-bold rounded-xl text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="w-2/3 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-600/30">
                        <span x-text="isEdit ? 'Update Category' : 'Save Category'"></span>
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function categoryManagement() {
        return {
            showModal: false,
            isEdit: false,
            formUrl: '',
            form: { id: null, name: '' },

            openCreateModal() {
                this.isEdit = false;
                this.formUrl = "{{ route('admin.categories.store') }}";
                this.form = { id: null, name: '' };
                this.showModal = true;
            },

            openEditModal(category) {
                this.isEdit = true;
                this.formUrl = `/admin/categories/${category.id}`;
                this.form = { id: category.id, name: category.name };
                this.showModal = true;
            }
        };
    }
</script>
@endpush
