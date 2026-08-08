@extends('layouts.app')
@section('title', 'Transactions')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold">All Transactions</h2>
        <div class="flex gap-2">
            <a href="{{ route('transactions.create', ['type' => 'income']) }}" class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i> Income
            </a>
            <a href="{{ route('transactions.create', ['type' => 'expense']) }}" class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white">
                <i data-lucide="plus" class="w-4 h-4"></i> Expense
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..."
               class="col-span-2 lg:col-span-2 px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        <select name="type" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
            <option value="">All Types</option>
            <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
            <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
        </select>
        <select name="category_id" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
            <option value="">All Categories</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        <div class="col-span-2 sm:col-span-3 lg:col-span-6 flex gap-2">
            <button class="px-4 py-2 text-sm font-medium rounded-lg bg-slate-900 dark:bg-slate-700 text-white">Filter</button>
            <a href="{{ route('transactions.index') }}" class="px-4 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Date</th>
                        <th class="text-left px-5 py-3 font-medium">Category</th>
                        <th class="text-left px-5 py-3 font-medium">Description</th>
                        <th class="text-left px-5 py-3 font-medium">Method</th>
                        <th class="text-right px-5 py-3 font-medium">Amount</th>
                        <th class="text-right px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($transactions as $t)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-5 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $t->date->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium" style="background-color: {{ ($t->category->color ?? '#94a3b8') }}20; color: {{ $t->category->color ?? '#94a3b8' }}">
                                    <i data-lucide="{{ $t->category->icon ?? 'circle' }}" class="w-3 h-3"></i>
                                    {{ $t->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-300 max-w-xs truncate">{{ $t->description ?: '—' }}</td>
                            <td class="px-5 py-3 text-slate-500 capitalize">{{ str_replace('_', ' ', $t->payment_method) }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $t->type === 'income' ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $t->type === 'income' ? '+' : '-' }}{{ auth()->user()->currencySymbol() }}{{ number_format($t->amount, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('transactions.edit', $t) }}" class="p-1.5 text-slate-400 hover:text-primary-600 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('transactions.destroy', $t) }}" onsubmit="return confirm('Delete this transaction?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-slate-400 hover:text-red-500 rounded-md hover:bg-slate-100 dark:hover:bg-slate-700">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-12 text-slate-400">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-700">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
