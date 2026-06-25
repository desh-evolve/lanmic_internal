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

    // Columns: A–M  (13 total)
    private const LAST_COL  = 'M';
    private const NUM_COLS  = 13;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

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

        $itemType = $this->filters['item_type'] ?? 'all';
        if ($itemType === 'import') {
            $query->where('requisition_issued_items.item_code', 'like', 'ENI-%');
        } elseif ($itemType === 'local') {
            $query->where('requisition_issued_items.item_code', 'not like', 'ENI-%');
        }

        $requestedBy = array_filter((array) ($this->filters['requested_by'] ?? []));
        if (!empty($requestedBy)) {
            $query->whereIn('r_sort.user_id', $requestedBy);
        }

        return $query
            ->orderBy('d_sort.name', 'asc')
            ->orderBy('requisition_issued_items.issued_at', 'asc')
            ->get()
            ->groupBy(fn($i) => $i->requisition->department->name ?? 'Unknown');
    }

    public function registerEvents(): array
    {
        $groupedData = $this->buildGroupedData();
        $lastCol     = self::LAST_COL;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($groupedData, $lastCol) {
                $sheet = $event->sheet->getDelegate();
                $row   = 1;

                // ── Column headers ────────────────────────────────────────
                $headers = [
                    'Document No', 'Date', 'Requested By',
                    'Item Code', 'Item Name', 'Type',
                    'Loc', 'UOM', 'Qty', 'Unit Price', 'Total Cost',
                    'Sub-Dept', 'Job Card', // M is last but let's recount...
                ];
                // 13 columns: A=Doc No, B=Date, C=Requested By, D=Item Code,
                // E=Item Name, F=Type, G=Loc, H=UOM, I=Qty, J=Unit Price,
                // K=Total Cost, L=Sub-Dept, M=Job Card  ... wait that's 13
                // Let me re-define clearly:
                $headers = [
                    'Document No',   // A
                    'Date',          // B
                    'Requested By',  // C
                    'Item Code',     // D
                    'Item Name',     // E
                    'Type',          // F
                    'Loc',           // G
                    'UOM',           // H
                    'Qty',           // I
                    'Unit Price',    // J
                    'Total Cost',    // K
                    'Sub-Dept',      // L
                    'Remarks',       // M
                ];
                $sheet->fromArray($headers, null, "A{$row}");
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(15);
                $row++;

                $grandTotalQty  = 0;
                $grandTotalCost = 0;

                foreach ($groupedData as $deptName => $items) {

                    // ── Department header ─────────────────────────────────
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->setCellValue("A{$row}", strtoupper($deptName));
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(14);
                    $row++;

                    $groupQty  = 0;
                    $groupCost = 0;

                    foreach ($items as $item) {
                        $isImport = str_starts_with($item->item_code ?? '', 'ENI-');

                        $sheet->fromArray([
                            $item->requisition->requisition_number ?? '',  // A
                            Carbon::parse($item->issued_at)->format('d/m/Y'), // B
                            $item->requisition->user->name ?? '',          // C
                            $item->item_code ?? '',                        // D
                            $item->item_name,                              // E
                            $isImport ? 'Import' : 'Local',                // F
                            $item->location_code ?? '',                    // G
                            $item->unit ?? '',                             // H
                            (float) $item->issued_quantity,                // I
                            (float) $item->unit_price,                     // J
                            (float) $item->total_price,                    // K
                            $item->requisition->subDepartment->name ?? '', // L
                            $item->notes ?? '',                            // M
                        ], null, "A{$row}");

                        // Type column colour
                        $sheet->getStyle("F{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $isImport ? '0D6EFD' : '28A745']],
                        ]);

                        // Numeric columns right-aligned
                        $sheet->getStyle("I{$row}")->getNumberFormat()
                              ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                        $sheet->getStyle("J{$row}:K{$row}")->getNumberFormat()
                              ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                        $sheet->getStyle("I{$row}:K{$row}")->getAlignment()
                              ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                        $groupQty  += $item->issued_quantity;
                        $groupCost += $item->total_price;
                        $row++;
                    }

                    // ── Group subtotal ────────────────────────────────────
                    $sheet->mergeCells("A{$row}:H{$row}");
                    $sheet->setCellValue("I{$row}", (float) $groupQty);
                    $sheet->setCellValue("K{$row}", (float) $groupCost);

                    $sheet->getStyle("I{$row}")->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle("K{$row}")->getNumberFormat()
                          ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle("I{$row}:K{$row}")->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font'    => ['bold' => true],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                    ]);
                    $row++;

                    // ── Blank spacer ──────────────────────────────────────
                    $sheet->getRowDimension($row)->setRowHeight(6);
                    $row++;

                    $grandTotalQty  += $groupQty;
                    $grandTotalCost += $groupCost;
                }

                // ── Grand total ───────────────────────────────────────────
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->setCellValue("A{$row}", 'TOTAL ISSUING COST');
                $sheet->setCellValue("I{$row}", (float) $grandTotalQty);
                $sheet->setCellValue("K{$row}", (float) $grandTotalCost);

                $sheet->getStyle("I{$row}")->getNumberFormat()
                      ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("K{$row}")->getNumberFormat()
                      ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle("I{$row}:K{$row}")->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(16);

                // ── Outer borders + wrap item name ────────────────────────
                $sheet->getStyle("A1:{$lastCol}{$row}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BFBFBF']],
                    ],
                ]);
                $sheet->getStyle('E1:E' . $row)->getAlignment()->setWrapText(true);
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
            'C' => 22,  // Requested By
            'D' => 16,  // Item Code
            'E' => 34,  // Item Name
            'F' => 9,   // Type
            'G' => 7,   // Loc
            'H' => 7,   // UOM
            'I' => 10,  // Qty
            'J' => 13,  // Unit Price
            'K' => 15,  // Total Cost
            'L' => 22,  // Sub-Dept
            'M' => 28,  // Remarks
        ];
    }
}
