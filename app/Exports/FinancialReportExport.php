<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinancialReportExport implements WithMultipleSheets
{
    public function __construct(
        protected int $userId,
        protected string $from,
        protected string $to,
    ) {}

    public function sheets(): array
    {
        return [
            'Summary' => new SummarySheetExport($this->userId, $this->from, $this->to),
            'Transactions' => new TransactionsSheetExport($this->userId, $this->from, $this->to),
            'By Category' => new CategoryBreakdownSheetExport($this->userId, $this->from, $this->to),
            'Monthly Trend' => new MonthlyTrendSheetExport($this->userId, $this->to),
        ];
    }
}
