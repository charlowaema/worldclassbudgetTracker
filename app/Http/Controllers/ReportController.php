<?php

namespace App\Http\Controllers;

use App\Exports\FinancialReportExport;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $transactions = Transaction::forUser($user->id)
            ->with('category')
            ->betweenDates($from, $to)
            ->orderBy('date')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        $byCategory = $transactions->where('type', 'expense')
            ->groupBy(fn ($t) => $t->category->name ?? 'Uncategorized')
            ->map(fn ($group) => $group->sum('amount'))
            ->sortDesc();

        // Monthly summary for the last 12 months (for the yearly view/export)
        $monthlySummary = [];
        $cursor = Carbon::parse($to)->startOfMonth()->subMonths(11);
        for ($i = 0; $i < 12; $i++) {
            $inc = Transaction::forUser($user->id)->inMonth($cursor->month, $cursor->year)->income()->sum('amount');
            $exp = Transaction::forUser($user->id)->inMonth($cursor->month, $cursor->year)->expense()->sum('amount');
            $monthlySummary[] = [
                'label' => $cursor->format('M Y'),
                'income' => (float) $inc,
                'expense' => (float) $exp,
                'net' => (float) ($inc - $exp),
            ];
            $cursor->addMonth();
        }

        return view('reports.index', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'byCategory' => $byCategory,
            'monthlySummary' => $monthlySummary,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /** Stream a multi-sheet Excel workbook: Transactions, Category Breakdown, Monthly Summary. */
    public function exportExcel(Request $request)
    {
        $user = $request->user();

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $filename = 'budget-report_' . $from . '_to_' . $to . '.xlsx';

        return Excel::download(new FinancialReportExport($user->id, $from, $to), $filename);
    }
}
