<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlyTrendSheetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected int $userId,
        protected string $to,
    ) {}

    public function collection(): Collection
    {
        $rows = collect();
        $cursor = Carbon::parse($this->to)->startOfMonth()->subMonths(11);

        for ($i = 0; $i < 12; $i++) {
            $income = Transaction::forUser($this->userId)
                ->inMonth($cursor->month, $cursor->year)->income()->sum('amount');
            $expense = Transaction::forUser($this->userId)
                ->inMonth($cursor->month, $cursor->year)->expense()->sum('amount');

            $rows->push((object) [
                'label' => $cursor->format('M Y'),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'net' => (float) ($income - $expense),
            ]);

            $cursor->addMonth();
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Month', 'Income', 'Expense', 'Net Savings'];
    }

    public function map($row): array
    {
        return [$row->label, $row->income, $row->expense, $row->net];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('A1:D1')->getFont()->getColor()->setRGB('FFFFFF');

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('B2:D' . $highestRow)->getNumberFormat()->setFormatCode('#,##0.00');

        return [];
    }

    public function title(): string
    {
        return 'Monthly Trend';
    }
}
