<?php

namespace App\Exports;

use App\Models\RequisitionIssuedItem;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class IssuedItemsExport implements WithEvents, WithTitle, WithColumnWidths
{
    protected array $filters;

    // Columns: A–K  (11 total)
    private const LAST_COL  = 'K';
    private const NUM_COLS  = 11;
    private const COL_RANGE = 'A:K';

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    // ── Build grouped data ────────────────────────────────────────────────
    protected function buildGroupedData(): \Illuminate\Support\Collection
    {
        $query = RequisitionIssuedItem::with([
                'requisition.user',
                'requisition.department',
                'requisition.subDepartment',
                'issuedBy',
            ])
            ->join('requisitions as r_sort', 'requisition_issued_items.requisition_id', '=', 'r_sort.id')
            ->join('departments as d_sort', 'r_sort.department_id', '=', 'd_sort.id')
            ->select('requisition_issued_items.*')
            ->where('requisition_issued_items.status', '!=', 'delete');

        if (!empty($this->filters['date_from']))
            $query->whereDate('requisition_issued_items.issued_at', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))
            $query->whereDate('requisition_issued_items.issued_at', '<=', $this->filters['date_to']);
        if (!empty($this->filters['item_code']))
            $query->where('requisition_issued_items.item_code', 'like', '%' . $this->filters['item_code'] . '%');
        if (!empty($this->filters['item_name']))
            $query->where('requisition_issued_items.item_name', 'like', '%' . $this->filters['item_name'] . '%');
        if (!empty($this->filters['department_id']))
            $query->where('r_sort.department_id', $this->filters['department_id']);
        // Import = item code starts with 'ENI-'; local = everything else
        if (!empty($this->filters['item_type'])) {
            if ($this->filters['item_type'] === 'import') {
                $query->where('requisition_issued_items.item_code', 'like', 'ENI-%');
            } elseif ($this->filters['item_type'] === 'local') {
                $query->where('requisition_issued_items.item_code', 'not like', 'ENI-%');
            }
        }

        $items = $query
            ->orderBy('d_sort.name', 'asc')
            ->orderBy('requisition_issued_items.issued_at', 'asc')
            ->get();

        return $items->groupBy(fn($i) => $i->requisition->department->name ?? 'Unknown');
    }

    // ── Build the sheet row by row ────────────────────────────────────────
    public function registerEvents(): array
    {
        $groupedData = $this->buildGroupedData();

        return [
            AfterSheet::class => function (AfterSheet $event) use ($groupedData) {
                $sheet = $event->sheet->getDelegate();
                $row   = 1;

                // ── Column header row ─────────────────────────────────────
                $headers = [
                    'Document No', 'Date', 'Item', 'Loc', 'UOM',
                    'Qty', 'Unit Price', 'Total Cost',
                    'Sub-Dept', 'Job Card', 'Remarks',
                ];
                $sheet->fromArray($headers, null, "A{$row}");
                $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2F5597'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(15);
                $row++;

                $grandTotalQty  = 0;
                $grandTotalCost = 0;

                foreach ($groupedData as $deptName => $items) {

                    // ── Department group header ───────────────────────────
                    $sheet->mergeCells("A{$row}:K{$row}");
                    $sheet->setCellValue("A{$row}", strtoupper($deptName));
                    $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D9D9D9'],
                        ],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(14);
                    $row++;

                    $groupQty  = 0;
                    $groupCost = 0;

                    // ── Data rows ─────────────────────────────────────────
                    foreach ($items as $item) {
                        $sheet->fromArray([
                            $item->requisition->requisition_number ?? '',
                            Carbon::parse($item->issued_at)->format('d/m/Y'),
                            $item->item_name,
                            $item->location_code ?? '',
                            $item->unit          ?? '',
                            (float) $item->issued_quantity,
                            (float) $item->unit_price,
                            (float) $item->total_price,
                            $item->requisition->subDepartment->name ?? '',
                            $item->reference_number_1 ?? '',
                            $item->notes ?? '',
                        ], null, "A{$row}");

                        // Right-align + number format for numeric columns
                        $sheet->getStyle("F{$row}")->getNumberFormat()
                              ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                        $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()
                              ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                        $sheet->getStyle("F{$row}:H{$row}")->getAlignment()
                              ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                        $groupQty  += $item->issued_quantity;
                        $groupCost += $item->total_price;
                        $row++;
                    }

                    // ── Group subtotal ────────────────────────────────────
                    $sheet->mergeCells("A{$row}:E{$row}");
                    $sheet->setCellValue("F{$row}", (float) $groupQty);
                    $sheet->setCellValue("H{$row}", (float) $groupCost);

                    $sheet->getStyle("F{$row}")->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle("H{$row}")->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle("F{$row}:H{$row}")->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                        'font'    => ['bold' => true],
                        'borders' => [
                            'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                        ],
                    ]);
                    $row++;

                    // ── Blank spacer between groups ───────────────────────
                    $sheet->getRowDimension($row)->setRowHeight(6);
                    $row++;

                    $grandTotalQty  += $groupQty;
                    $grandTotalCost += $groupCost;
                }

                // ── Grand total row ───────────────────────────────────────
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", 'TOTAL ISSUING COST');
                $sheet->setCellValue("F{$row}", (float) $grandTotalQty);
                $sheet->setCellValue("H{$row}", (float) $grandTotalCost);

                $sheet->getStyle("F{$row}")->getNumberFormat()
                      ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("H{$row}")->getNumberFormat()
                      ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("F{$row}:H{$row}")->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'],  // yellow — same as spreadsheet
                    ],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']],
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(16);

                // ── Apply outer border to entire data range ───────────────
                $sheet->getStyle("A1:K{$row}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => 'BFBFBF'],
                        ],
                    ],
                ]);

                // ── Wrap text in Item column (C) ──────────────────────────
                $sheet->getStyle('C1:C' . $row)->getAlignment()->setWrapText(true);

                // ── Freeze header row ─────────────────────────────────────
                $sheet->freezePane('A2');
            },
        ];
    }

    public function title(): string
    {
        return 'Issued Items';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // Document No
            'B' => 11,  // Date
            'C' => 38,  // Item
            'D' => 7,   // Loc
            'E' => 7,   // UOM
            'F' => 10,  // Qty
            'G' => 13,  // Unit Price
            'H' => 15,  // Total Cost
            'I' => 22,  // Sub-Dept
            'J' => 14,  // Job Card
            'K' => 28,  // Remarks
        ];
    }
}
