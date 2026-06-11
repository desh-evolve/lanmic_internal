<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class InventoryMovementExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $grouped;
    protected array $openingBalances;
    protected string $dateFrom;
    protected string $dateTo;

    public function __construct($grouped, array $openingBalances, string $dateFrom, string $dateTo)
    {
        $this->grouped         = $grouped;
        $this->openingBalances = $openingBalances;
        $this->dateFrom        = $dateFrom;
        $this->dateTo          = $dateTo;
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->grouped as $itemCode => $movements) {
            $firstRow   = $movements->first();
            $openingQty = $this->openingBalances[$itemCode] ?? null;

            // Item header row
            $rows[] = [
                '── ' . $itemCode . ' — ' . ($firstRow['item_name'] ?? ''),
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            ];

            // Opening balance row
            if ($openingQty !== null) {
                $rows[] = [
                    Carbon::parse($this->dateFrom)->format('d M Y'),
                    'Opening Balance',
                    '—', '—', '—', '—',
                    number_format($openingQty, 4),   // Qty In col (used as balance)
                    '',
                    '', '',
                    '', '', '', '', '', '',
                ];
            }

            // Movement rows
            foreach ($movements->sortBy('date') as $row) {
                $rows[] = [
                    $row['date'] ? Carbon::parse($row['date'])->format('d M Y') : '',
                    $row['document_no'],
                    $row['type'],
                    $row['unit'],
                    $row['invoice_no'],
                    $row['vendor_no'],
                    $row['qty_in']  > 0 ? $row['qty_in']  : '',
                    $row['cost_in'] > 0 ? number_format($row['cost_in'],  2) : '',
                    $row['qty_out']  > 0 ? $row['qty_out']  : '',
                    $row['cost_out'] > 0 ? number_format($row['cost_out'], 2) : '',
                    $row['vendor_name'],
                    $row['department'],
                    $row['sub_dept'],
                    $row['remarks'],
                ];
            }

            // Subtotals
            $totalQtyIn   = $movements->sum('qty_in');
            $totalCostIn  = $movements->sum('cost_in');
            $totalQtyOut  = $movements->sum('qty_out');
            $totalCostOut = $movements->sum('cost_out');

            $rows[] = [
                '', 'Item Total', '', '', '', '',
                $totalQtyIn  > 0 ? number_format($totalQtyIn,  4) : '',
                $totalCostIn > 0 ? number_format($totalCostIn, 2) : '',
                $totalQtyOut  > 0 ? number_format($totalQtyOut,  4) : '',
                $totalCostOut > 0 ? number_format($totalCostOut, 2) : '',
                '', '', '', '',
            ];

            // Closing balance
            if ($openingQty !== null) {
                $closingQty = $openingQty + $totalQtyIn - $totalQtyOut;
                $rows[] = [
                    Carbon::parse($this->dateTo)->format('d M Y'),
                    'Closing Balance', '', '', '', '',
                    number_format($closingQty, 4),
                    '', '', '',
                    '', '', '', '',
                ];
            }

            // Blank spacer between items
            $rows[] = array_fill(0, 14, '');
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Document No',
            'Type',
            'Unit',
            'Invoice No',
            'Vendor No',
            'Qty In',
            'Cost In',
            'Qty Out',
            'Cost Out',
            'Vendor Name',
            'Department',
            'Sub-Department',
            'Remarks',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '343A40'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14, 'B' => 25, 'C' => 16, 'D' => 8,
            'E' => 18, 'F' => 14, 'G' => 12, 'H' => 14,
            'I' => 12, 'J' => 14, 'K' => 28, 'L' => 25,
            'M' => 25, 'N' => 35,
        ];
    }

    public function title(): string
    {
        return 'Inventory Movement';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                for ($r = 2; $r <= $highestRow; $r++) {
                    $cellA = $sheet->getCell("A{$r}")->getValue();
                    $cellB = $sheet->getCell("B{$r}")->getValue();

                    if (str_starts_with((string) $cellA, '──')) {
                        // Item header
                        $sheet->mergeCells("A{$r}:N{$r}");
                        $sheet->getStyle("A{$r}:N{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '495057']],
                        ]);
                    } elseif ($cellB === 'Opening Balance') {
                        $sheet->getStyle("A{$r}:N{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'italic' => true, 'color' => ['rgb' => '5D4037']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
                        ]);
                    } elseif ($cellB === 'Closing Balance') {
                        $sheet->getStyle("A{$r}:N{$r}")->applyFromArray([
                            'font'    => ['bold' => true, 'color' => ['rgb' => '1565C0']],
                            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
                            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1565C0']]],
                        ]);
                    } elseif ($cellB === 'Item Total') {
                        $sheet->getStyle("A{$r}:N{$r}")->applyFromArray([
                            'font'    => ['bold' => true],
                            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
                            'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '495057']]],
                        ]);
                    }
                }

                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter('A1:N1');
            },
        ];
    }
}
