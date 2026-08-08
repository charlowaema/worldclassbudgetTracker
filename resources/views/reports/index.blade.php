@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-bold">Reports</h2>
        <a href="{{ route('reports.export.excel', ['from' => $from, 'to' => $to]) }}"
           class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-600/30">
            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> Export to Excel
        </a>
    </div>

    <form method="GET" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1 text-slate-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900">
        </div>
        <button class="px-4 py-2 text-sm font-medium rounded-lg bg-slate-900 dark:bg-slate-700 text-white">Apply</button>
    </form>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <span class="text-sm text-slate-500">Total Income</span>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ auth()->user()->currencySymbol() }}{{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <span class="text-sm text-slate-500">Total Expense</span>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ auth()->user()->currencySymbol() }}{{ number_format($totalExpense, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <span class="text-sm text-slate-500">Net Savings</span>
            <p class="text-2xl font-bold mt-1 {{ ($totalIncome - $totalExpense) >= 0 ? '' : 'text-red-500' }}">{{ auth()->user()->currencySymbol() }}{{ number_format($totalIncome - $totalExpense, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <h3 class="font-semibold mb-4">12-Month Trend</h3>
            <canvas id="yearlyTrendChart" height="180"></canvas>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-100 dark:border-slate-700 shadow-sm">
            <h3 class="font-semibold mb-4">Expense by Category ({{ \Carbon\Carbon::parse($from)->format('d M') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }})</h3>
            @if ($byCategory->isEmpty())
                <p class="text-sm text-slate-400 text-center py-16">No expense data for this period</p>
            @else
                <div class="space-y-3">
                    @foreach ($byCategory as $name => $amount)
                        @php $pct = $totalExpense > 0 ? round(($amount / $totalExpense) * 100, 1) : 0; @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-medium">{{ $name }}</span>
                                <span class="text-slate-500">{{ auth()->user()->currencySymbol() }}{{ number_format($amount, 2) }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-primary-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Transactions table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <h3 class="font-semibold p-5 pb-0">Transactions in Range ({{ $transactions->count() }})</h3>
        <div class="overflow-x-auto mt-3">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3 font-medium">Date</th>
                        <th class="text-left px-5 py-3 font-medium">Category</th>
                        <th class="text-left px-5 py-3 font-medium">Description</th>
                        <th class="text-right px-5 py-3 font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($transactions->take(50) as $t)
                        <tr>
                            <td class="px-5 py-2.5 text-slate-600 dark:text-slate-300">{{ $t->date->format('d M Y') }}</td>
                            <td class="px-5 py-2.5">{{ $t->category->name ?? 'Uncategorized' }}</td>
                            <td class="px-5 py-2.5 text-slate-600 dark:text-slate-300">{{ $t->description ?: '—' }}</td>
                            <td class="px-5 py-2.5 text-right font-medium {{ $t->type === 'income' ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $t->type === 'income' ? '+' : '-' }}{{ auth()->user()->currencySymbol() }}{{ number_format($t->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-10 text-slate-400">No transactions in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transactions->count() > 50)
            <p class="text-xs text-slate-400 px-5 py-3">Showing first 50 of {{ $transactions->count() }} — export to Excel for the full list.</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('load', function () {
        new Chart(document.getElementById('yearlyTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode(collect($monthlySummary)->pluck('label')) !!},
                datasets: [
                    { label: 'Income', data: {!! json_encode(collect($monthlySummary)->pluck('income')) !!}, borderColor: '#22c55e', backgroundColor: '#22c55e20', fill: true, tension: 0.3 },
                    { label: 'Expense', data: {!! json_encode(collect($monthlySummary)->pluck('expense')) !!}, borderColor: '#ef4444', backgroundColor: '#ef444420', fill: true, tension: 0.3 },
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    });
</script>
@endpush
@endsection
