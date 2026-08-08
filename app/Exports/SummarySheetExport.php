<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class SummarySheetExport implements FromView, WithTitle
{
    public function __construct(
        protected int $userId,
        protected string $from,
        protected string $to,
    ) {}

    public function view(): View
    {
        $income = Transaction::forUser($this->userId)->income()->betweenDates($this->from, $this->to)->sum('amount');
        $expense = Transaction::forUser($this->userId)->expense()->betweenDates($this->from, $this->to)->sum('amount');

        return view('exports.summary', [
            'from' => $this->from,
            'to' => $this->to,
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'generatedAt' => now()->format('d M Y, H:i'),
        ]);
    }

    public function title(): string
    {
        return 'Summary';
    }
}
