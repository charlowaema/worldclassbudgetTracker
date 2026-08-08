@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold">Categories</h2>
        <button @click="showModal = true" class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white">
            <i data-lucide="plus" class="w-4 h-4"></i> New Category
        </button>
    </div>

    @foreach (['expense' => 'Expense Categories', 'income' => 'Income Categories'] as $type => $label)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
            <h3 class="font-semibold mb-4">{{ $label }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse ($categories[$type] ?? [] as $c)
                    <div class="flex items-center justify-between gap-2 p-3 rounded-lg border border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: {{ $c->color }}20">
                                <i data-lucide="{{ $c->icon }}" class="w-4 h-4" style="color: {{ $c->color }}"></i>
                            </div>
                            <span class="text-sm font-medium truncate">{{ $c->name }}</span>
                        </div>
                        @if ($c->user_id)
                            <form method="POST" action="{{ route('categories.destroy', $c) }}" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="p-1 text-slate-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        @else
                            <span class="text-[10px] uppercase font-semibold text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">Default</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400 col-span-full">No categories yet.</p>
                @endforelse
            </div>
        </div>
    @endforeach

    {{-- New category modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="showModal = false" class="absolute inset-0 bg-black/40"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-bold text-lg mb-4">New Category</h3>
            <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1.5">Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Type</label>
                    <select name="type" class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                        <option value="expense">Expense</option>
                        <option value="income">Income</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Icon (lucide name)</label>
                        <input type="text" name="icon" value="tag" class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5">Color</label>
                        <input type="color" name="color" value="#6366f1" class="w-full h-[42px] px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2.5 rounded-lg bg-primary-600 text-white text-sm font-semibold">Create</button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
