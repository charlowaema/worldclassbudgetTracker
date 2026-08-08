@extends('layouts.app')
@section('title', 'Budgets')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex items-center gap-2">
            <h2 class="text-xl font-bold mr-2">Budgets</h2>
            <select name="month" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                @foreach (range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                @foreach (range(now()->year, now()->year - 4) as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
        <button @click="showModal = true" class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white">
            <i data-lucide="plus" class="w-4 h-4"></i> Set Budget
        </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($budgets as $b)
            @php $pct = $b->percentUsed(); @endphp
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background-color: {{ $b->category->color }}20">
                            <i data-lucide="{{ $b->category->icon }}" class="w-4 h-4" style="color: {{ $b->category->color }}"></i>
                        </div>
                        <span class="font-semibold text-sm">{{ $b->category->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('budgets.destroy', $b) }}" onsubmit="return confirm('Remove this budget?')">
                        @csrf @method('DELETE')
                        <button class="p-1 text-slate-400 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
                <div class="flex justify-between text-xs mb-1.5 text-slate-500">
                    <span>{{ auth()->user()->currencySymbol() }}{{ number_format($b->spentAmount(), 2) }} spent</span>
                    <span>{{ auth()->user()->currencySymbol() }}{{ number_format($b->amount, 2) }} budget</span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs mt-2 {{ $b->remaining() < 0 ? 'text-red-500' : 'text-slate-500' }}">
                    {{ $b->remaining() >= 0 ? auth()->user()->currencySymbol() . number_format($b->remaining(), 2) . ' remaining' : 'Over budget by ' . auth()->user()->currencySymbol() . number_format(abs($b->remaining()), 2) }}
                </p>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700">
                <i data-lucide="target" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                <p class="text-sm text-slate-400">No budgets set for this month yet.</p>
            </div>
        @endforelse
    </div>

    {{-- Set budget modal --}}
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="showModal = false" class="absolute inset-0 bg-black/40"></div>
        <div class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="font-bold text-lg mb-4">Set Monthly Budget</h3>
            <form method="POST" action="{{ route('budgets.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <div>
                    <label class="block text-sm font-medium mb-1.5">Category</label>
                    <select name="category_id" required class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Monthly Limit</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2.5 rounded-lg bg-primary-600 text-white text-sm font-semibold">Save Budget</button>
                    <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
