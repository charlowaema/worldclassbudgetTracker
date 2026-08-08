<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoryBreakdownSheetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected int $userId,
        protected string $from,
        protected string $to,
    ) {}

    public function collection(): Collection
    {
        $totalExpense = Transaction::forUser($this->userId)
            ->expense()->betweenDates($this->from, $this->to)->sum('amount');

        return Transaction::forUser($this->userId)
            ->with('category')
            ->expense()
            ->betweenDates($this->from, $this->to)
            ->get()
            ->groupBy(fn ($t) => $t->category->name ?? 'Uncategorized')
            ->map(function ($group, $name) use ($totalExpense) {
                $sum = $group->sum('amount');

                return (object) [
                    'category' => $name,
                    'total' => $sum,
                    'count' => $group->count(),
                    'percent' => $totalExpense > 0 ? round(($sum / $totalExpense) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    public function headings(): array
    {
        return ['Category', 'Total Spent', 'Transaction Count', '% of Total Spend'];
    }

    public function map($row): array
    {
        return [$row->category, (float) $row->total, $row->count, $row->percent . '%'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('A1:D1')->getFont()->getColor()->setRGB('FFFFFF');

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('B2:B' . $highestRow)->getNumberFormat()->setFormatCode('#,##0.00');

        return [];
    }

    public function title(): string
    {
        return 'By Category';
    }
}
