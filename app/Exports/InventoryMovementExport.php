<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class InventoryMovementExport implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    protected $grouped;
    protected array $openingBalances;
    protected string $dateFrom;
    protected string $dateTo;

    // Track which rows need which style (set during array() build)
    protected array $rowMeta = [];

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

        // Row 1: company / report title (merged later in AfterSheet)
        $rows[] = ['Lanka Minerals & Chemicals (Pvt) Ltd.', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->rowMeta[count($rows)] = 'title';

        $rows[] = ['I/C Inventory Movement (ICMVMT02)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        $this->rowMeta[count($rows)] = 'subtitle';

        $rows[] = [
            'From Date: [' . Carbon::parse($this->dateFrom)->format('d M Y') . '] To [' . Carbon::parse($this->dateTo)->format('d M Y') . ']',
            '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
        ];
        $this->rowMeta[count($rows)] = 'param';

        // Blank spacer
        $rows[] = array_fill(0, 16, '');

        // Group header row (Inventory In | Inventory Out | Balance)
        $rows[] = [
            'Date', 'Document Number', 'Srce. Appl.', 'Type', 'Unit',
            'Inventory In', '', 'Inventory Out', '', 'Balance', '',
            'Invoice No', 'Vendor No', 'Vendor Name', 'Department', 'Remarks',
        ];
        $this->rowMeta[count($rows)] = 'group-header';

        // Sub-header row
        $rows[] = [
            '', '', '', '', '',
            'Quantity', 'Extended Cost', 'Quantity', 'Extended Cost', 'Quantity', 'Actual Cost',
            '', '', '', '', '',
        ];
        $this->rowMeta[count($rows)] = 'sub-header';

        $dataStartRow = count($rows) + 1;

        foreach ($this->grouped as $itemCode => $movements) {
            $firstRow   = $movements->first();
            $openingQty = $this->openingBalances[$itemCode] ?? null;

            // Item header
            $rows[] = [
                $itemCode . '  (' . ($firstRow['item_name'] ?? '') . ')',
                '', '', '', '', '', '', '', '', '', '',
                '', '', '', 'Costing Method: ELS', '',
            ];
            $this->rowMeta[count($rows)] = 'item-header';

            // Opening Balance
            $rows[] = [
                Carbon::parse($this->dateFrom)->format('d/m/Y'),
                'Opening Balance', '', '', '',
                '—', '—', '—', '—',
                $openingQty !== null ? number_format($openingQty, 4) : 'N/A',
                '0.00',
                '', '', '', '', '',
            ];
            $this->rowMeta[count($rows)] = 'opening';

            // Movement rows
            $runningQty  = $openingQty ?? 0;
            $runningCost = 0;

            foreach ($movements->sortBy('date') as $row) {
                $runningQty  += $row['qty_in'] - $row['qty_out'];
                $runningCost += $row['cost_in'] - $row['cost_out'];

                $srcAppl = $row['type'] === 'Purchase GRN' ? 'PO' : 'IC';

                $rows[] = [
                    $row['date'] ? Carbon::parse($row['date'])->format('d/m/Y') : '',
                    $row['document_no'],
                    $srcAppl,
                    $row['type'],
                    $row['unit'],
                    $row['qty_in']  > 0 ? $row['qty_in']  : '',
                    $row['cost_in'] > 0 ? number_format($row['cost_in'],  2) : '',
                    $row['qty_out']  > 0 ? $row['qty_out']  : '',
                    $row['cost_out'] > 0 ? number_format($row['cost_out'], 2) : '',
                    number_format($runningQty, 4),
                    number_format($runningCost, 2),
                    $row['invoice_no'],
                    $row['vendor_no'],
                    $row['vendor_name'],
                    $row['department'],
                    $row['remarks'],
                ];
                $this->rowMeta[count($rows)] = match($row['type']) {
                    'RETURN GRN'   => 'grn',
                    'Purchase GRN' => 'po-grn',
                    default        => 'issue',
                };
            }

            // Item Total
            $totalQtyIn   = $movements->sum('qty_in');
            $totalCostIn  = $movements->sum('cost_in');
            $totalQtyOut  = $movements->sum('qty_out');
            $totalCostOut = $movements->sum('cost_out');

            $rows[] = [
                '', 'Item Total:', '', '', '',
                $totalQtyIn  > 0 ? number_format($totalQtyIn,  4) : '',
                $totalCostIn > 0 ? number_format($totalCostIn, 2) : '',
                $totalQtyOut  > 0 ? number_format($totalQtyOut,  4) : '',
                $totalCostOut > 0 ? number_format($totalCostOut, 2) : '',
                '', '',
                '', '', '', '', '',
            ];
            $this->rowMeta[count($rows)] = 'item-total';

            // Ending Balance
            $endingQty  = $openingQty !== null ? $openingQty + $totalQtyIn - $totalQtyOut : null;
            $endingCost = $totalCostIn - $totalCostOut;

            $rows[] = [
                Carbon::parse($this->dateTo)->format('d/m/Y'),
                'Ending Balance:', '', '', '',
                '', '', '', '',
                $endingQty !== null ? number_format($endingQty, 4) : 'N/A',
                number_format($endingCost, 2),
                '', '', '', '', '',
            ];
            $this->rowMeta[count($rows)] = 'ending';

            // Spacer
            $rows[] = array_fill(0, 16, '');
            $this->rowMeta[count($rows)] = 'spacer';
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 13, 'B' => 26, 'C' => 9,  'D' => 16,
            'E' => 7,  'F' => 12, 'G' => 14,  'H' => 12,
            'I' => 14, 'J' => 12, 'K' => 14,  'L' => 16,
            'M' => 13, 'N' => 26, 'O' => 24,  'P' => 28,
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
                $sheet      = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $lastCol    = 'P';

                // ── Title rows (1-3) ──────────────────────────────
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1a1a2e']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '343a40']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['size' => 9, 'color' => ['rgb' => '495057']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Row 4 is blank — skip

                // ── Group header row (row 5) ──────────────────────
                // Merge group labels
                $sheet->mergeCells('F5:G5'); // Inventory In
                $sheet->mergeCells('H5:I5'); // Inventory Out
                $sheet->mergeCells('J5:K5'); // Balance

                $groupHeaderRow = 5;
                // Style base columns A-E and L-P
                $sheet->getStyle("A{$groupHeaderRow}:E{$groupHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '495057']],
                ]);
                $sheet->getStyle("L{$groupHeaderRow}:{$lastCol}{$groupHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '495057']],
                ]);
                // Inventory In
                $sheet->getStyle("F{$groupHeaderRow}:G{$groupHeaderRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a6634']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                // Inventory Out
                $sheet->getStyle("H{$groupHeaderRow}:I{$groupHeaderRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7b1a23']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                // Balance
                $sheet->getStyle("J{$groupHeaderRow}:K{$groupHeaderRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0d3d6b']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Sub-header row (row 6) ────────────────────────
                $subHeaderRow = 6;
                $sheet->getStyle("A{$subHeaderRow}:E{$subHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '495057']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("F{$subHeaderRow}:G{$subHeaderRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => '155724']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'd4edda']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("H{$subHeaderRow}:I{$subHeaderRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => '721c24']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8d7da']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("J{$subHeaderRow}:K{$subHeaderRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => '004085']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'cce5ff']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle("L{$subHeaderRow}:{$lastCol}{$subHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '495057']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // ── Data rows ─────────────────────────────────────
                foreach ($this->rowMeta as $r => $type) {
                    if ($r <= 6) continue; // already styled above

                    $range = "A{$r}:{$lastCol}{$r}";

                    switch ($type) {
                        case 'item-header':
                            $sheet->mergeCells("A{$r}:O{$r}");
                            $sheet->getStyle($range)->applyFromArray([
                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '343a40']],
                            ]);
                            break;

                        case 'opening':
                            $sheet->getStyle($range)->applyFromArray([
                                'font' => ['bold' => true, 'italic' => true, 'color' => ['rgb' => '5D4037']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
                            ]);
                            // Right-align balance columns
                            $sheet->getStyle("J{$r}:K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            break;

                        case 'issue':
                            $sheet->getStyle($range)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'fff8f0']],
                            ]);
                            $this->applyMovementCellStyles($sheet, $r);
                            break;

                        case 'grn':
                            $sheet->getStyle($range)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'f0fff4']],
                            ]);
                            $this->applyMovementCellStyles($sheet, $r);
                            break;

                        case 'po-grn':
                            $sheet->getStyle($range)->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e8f4fd']],
                            ]);
                            $this->applyMovementCellStyles($sheet, $r);
                            break;

                        case 'item-total':
                            $sheet->getStyle($range)->applyFromArray([
                                'font'    => ['bold' => true],
                                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FA']],
                                'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '495057']]],
                            ]);
                            $sheet->getStyle("F{$r}:I{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            break;

                        case 'ending':
                            $sheet->getStyle($range)->applyFromArray([
                                'font'    => ['bold' => true, 'color' => ['rgb' => '1565C0']],
                                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
                                'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1565C0']]],
                            ]);
                            $sheet->getStyle("J{$r}:K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                            break;
                    }
                }

                // Borders on the whole data range
                $sheet->getStyle("A5:{$lastCol}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DEE2E6']],
                    ],
                ]);

                $event->sheet->freezePane('A7');
            },
        ];
    }

    private function applyMovementCellStyles($sheet, int $r): void
    {
        // Inventory In (F,G) — green text
        $sheet->getStyle("F{$r}:G{$r}")->applyFromArray([
            'font'      => ['color' => ['rgb' => '155724']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        // Inventory Out (H,I) — red text
        $sheet->getStyle("H{$r}:I{$r}")->applyFromArray([
            'font'      => ['color' => ['rgb' => '721c24']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        // Balance (J,K) — blue text
        $sheet->getStyle("J{$r}:K{$r}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => '004085']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
    }
}
