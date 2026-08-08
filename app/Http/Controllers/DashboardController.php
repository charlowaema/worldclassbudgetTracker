<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $period = Carbon::createFromDate($year, $month, 1);
        $prevPeriod = $period->copy()->subMonth();

        $baseQuery = Transaction::forUser($user->id)->inMonth($month, $year);

        $totalIncome = (clone $baseQuery)->income()->sum('amount');
        $totalExpense = (clone $baseQuery)->expense()->sum('amount');
        $balance = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? round((($totalIncome - $totalExpense) / $totalIncome) * 100, 1) : 0;

        $prevIncome = Transaction::forUser($user->id)
            ->inMonth($prevPeriod->month, $prevPeriod->year)->income()->sum('amount');
        $prevExpense = Transaction::forUser($user->id)
            ->inMonth($prevPeriod->month, $prevPeriod->year)->expense()->sum('amount');

        $incomeChange = $prevIncome > 0 ? round((($totalIncome - $prevIncome) / $prevIncome) * 100, 1) : null;
        $expenseChange = $prevExpense > 0 ? round((($totalExpense - $prevExpense) / $prevExpense) * 100, 1) : null;

        // Expense breakdown by category (for pie/donut chart)
        $categoryBreakdown = Transaction::forUser($user->id)
            ->inMonth($month, $year)
            ->expense()
            ->selectRaw('category_id, SUM(amount) as total')
            ->with('category:id,name,color,icon')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        // Last 6 months trend (income vs expense) for line/bar chart
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = $period->copy()->subMonths($i);
            $inc = Transaction::forUser($user->id)->inMonth($d->month, $d->year)->income()->sum('amount');
            $exp = Transaction::forUser($user->id)->inMonth($d->month, $d->year)->expense()->sum('amount');
            $trend[] = [
                'label' => $d->format('M Y'),
                'income' => (float) $inc,
                'expense' => (float) $exp,
            ];
        }

        $recentTransactions = Transaction::forUser($user->id)
            ->with('category')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->where('year', $year)
            ->with('category')
            ->get();

        return view('dashboard', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'balance' => $balance,
            'savingsRate' => $savingsRate,
            'incomeChange' => $incomeChange,
            'expenseChange' => $expenseChange,
            'categoryBreakdown' => $categoryBreakdown,
            'trend' => $trend,
            'recentTransactions' => $recentTransactions,
            'budgets' => $budgets,
            'month' => $month,
            'year' => $year,
            'periodLabel' => $period->format('F Y'),
        ]);
    }
}
