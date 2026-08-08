@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Period selector --}}
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <h2 class="text-xl font-bold mr-auto">{{ $periodLabel }}</h2>
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

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Total Income</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4 text-emerald-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold mt-2">{{ auth()->user()->currencySymbol() }}{{ number_format($totalIncome, 2) }}</p>
            @if ($incomeChange !== null)
                <p class="text-xs mt-1 {{ $incomeChange >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    {{ $incomeChange >= 0 ? '↑' : '↓' }} {{ abs($incomeChange) }}% vs last month
                </p>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Total Expense</span>
                <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 flex items-center justify-center">
                    <i data-lucide="trending-down" class="w-4 h-4 text-red-500"></i>
                </div>
            </div>
            <p class="text-2xl font-bold mt-2">{{ auth()->user()->currencySymbol() }}{{ number_format($totalExpense, 2) }}</p>
            @if ($expenseChange !== null)
                <p class="text-xs mt-1 {{ $expenseChange <= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    {{ $expenseChange >= 0 ? '↑' : '↓' }} {{ abs($expenseChange) }}% vs last month
                </p>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Net Balance</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                    <i data-lucide="scale" class="w-4 h-4 text-indigo-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold mt-2 {{ $balance >= 0 ? 'text-slate-900 dark:text-white' : 'text-red-500' }}">
                {{ auth()->user()->currencySymbol() }}{{ number_format($balance, 2) }}
            </p>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Savings Rate</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center">
                    <i data-lucide="piggy-bank" class="w-4 h-4 text-purple-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold mt-2">{{ $savingsRate }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Trend chart --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <h3 class="font-semibold mb-4">Income vs Expense — Last 6 Months</h3>
            <canvas id="trendChart" height="110"></canvas>
        </div>

        {{-- Category breakdown --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <h3 class="font-semibold mb-4">Spending by Category</h3>
            @if ($categoryBreakdown->isEmpty())
                <p class="text-sm text-slate-400 text-center py-10">No expenses this period</p>
            @else
                <canvas id="categoryChart" height="200"></canvas>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent transactions --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-5 pb-0">
                <h3 class="font-semibold">Recent Transactions</h3>
                <a href="{{ route('transactions.index') }}" class="text-sm text-primary-600 font-medium">View all</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700 mt-3">
                @forelse ($recentTransactions as $t)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: {{ ($t->category->color ?? '#94a3b8') }}20">
                                <i data-lucide="{{ $t->category->icon ?? 'circle' }}" class="w-4 h-4" style="color: {{ $t->category->color ?? '#94a3b8' }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $t->description ?: ($t->category->name ?? 'Uncategorized') }}</p>
                                <p class="text-xs text-slate-500">{{ $t->category->name ?? 'Uncategorized' }} · {{ $t->date->format('d M') }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold {{ $t->type === 'income' ? 'text-emerald-600' : 'text-slate-700 dark:text-slate-200' }}">
                            {{ $t->type === 'income' ? '+' : '-' }}{{ auth()->user()->currencySymbol() }}{{ number_format($t->amount, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-10">No transactions yet — add your first one!</p>
                @endforelse
            </div>
        </div>

        {{-- Budget progress --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Budget Progress</h3>
                <a href="{{ route('budgets.index') }}" class="text-sm text-primary-600 font-medium">Manage</a>
            </div>
            <div class="space-y-4">
                @forelse ($budgets as $b)
                    @php $pct = $b->percentUsed(); @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium">{{ $b->category->name }}</span>
                            <span class="text-slate-500">{{ auth()->user()->currencySymbol() }}{{ number_format($b->spentAmount(),0) }} / {{ number_format($b->amount,0) }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6">No budgets set for this month</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('load', function () {
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(collect($trend)->pluck('label')) !!},
                    datasets: [
                        { label: 'Income', data: {!! json_encode(collect($trend)->pluck('income')) !!}, backgroundColor: '#22c55e', borderRadius: 6 },
                        { label: 'Expense', data: {!! json_encode(collect($trend)->pluck('expense')) !!}, backgroundColor: '#ef4444', borderRadius: 6 },
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        const catCtx = document.getElementById('categoryChart');
        if (catCtx) {
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoryBreakdown->pluck('category.name')) !!},
                    datasets: [{
                        data: {!! json_encode($categoryBreakdown->pluck('total')) !!},
                        backgroundColor: {!! json_encode($categoryBreakdown->pluck('category.color')) !!},
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
                }
            });
        }
    });
</script>
@endpush
@endsection
