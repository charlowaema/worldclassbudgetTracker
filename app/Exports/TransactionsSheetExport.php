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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsSheetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        protected int $userId,
        protected string $from,
        protected string $to,
    ) {}

    public function collection(): Collection
    {
        return Transaction::forUser($this->userId)
            ->with('category')
            ->betweenDates($this->from, $this->to)
            ->orderBy('date')
            ->get();
    }

    public function headings(): array
    {
        return ['Date', 'Type', 'Category', 'Description', 'Payment Method', 'Amount', 'Notes'];
    }

    public function map($transaction): array
    {
        return [
            $transaction->date->format('Y-m-d'),
            ucfirst($transaction->type),
            $transaction->category->name ?? 'Uncategorized',
            $transaction->description,
            str_replace('_', ' ', ucfirst($transaction->payment_method)),
            $transaction->type === 'expense' ? -1 * (float) $transaction->amount : (float) $transaction->amount,
            $transaction->notes,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1E293B');
        $sheet->getStyle('A1:G1')->getFont()->getColor()->setRGB('FFFFFF');

        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('F2:F' . $highestRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A1:G' . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

        return [];
    }

    public function title(): string
    {
        return 'Transactions';
    }
}
