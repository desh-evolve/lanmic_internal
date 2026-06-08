<?php

namespace App\Exports;

use App\Models\RequisitionIssuedItem;
use App\Models\GrnItem;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class InventoryMovementExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $dateFrom;
    protected $dateTo;
    protected $itemCode;
    protected $deptId;

    public function __construct($dateFrom, $dateTo, $itemCode = null, $deptId = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
        $this->itemCode = $itemCode;
        $this->deptId   = $deptId;
    }

    public function array(): array
    {
        $issuesQuery = RequisitionIssuedItem::with(['requisition.department', 'requisition.subDepartment'])
            ->where('status', '!=', 'delete')
            ->whereDate('issued_at', '>=', $this->dateFrom)
            ->whereDate('issued_at', '<=', $this->dateTo);

        if ($this->itemCode) $issuesQuery->where('item_code', 'like', '%' . $this->itemCode . '%');
        if ($this->deptId)   $issuesQuery->whereHas('requisition', fn($q) => $q->where('department_id', $this->deptId));

        $grnQuery = GrnItem::with(['return.requisition.department'])
            ->where('status', '!=', 'delete')
            ->whereDate('processed_at', '>=', $this->dateFrom)
            ->whereDate('processed_at', '<=', $this->dateTo);

        if ($this->itemCode) $grnQuery->where('item_code', 'like', '%' . $this->itemCode . '%');

        $rows = [];

        foreach ($issuesQuery->orderBy('issued_at')->get() as $issue) {
            $rows[] = [
                $issue->item_code,
                $issue->item_name,
                Carbon::parse($issue->issued_at)->format('d M Y'),
                $issue->reference_number_1 ?? $issue->requisition?->requisition_number ?? '',
                'Internal Usage',
                $issue->unit ?? '',
                '',   // Qty In
                '',   // Cost In
                $issue->issued_quantity,
                number_format($issue->total_price, 2),
                $issue->reference_number_2 ?? '',
                '',   // Vendor No
                '',   // Vendor Name
                $issue->requisition?->department?->name ?? '',
                $issue->requisition?->subDepartment?->name ?? '',
                $issue->notes ?? '',
            ];
        }

        foreach ($grnQuery->orderBy('processed_at')->get() as $grn) {
            $rows[] = [
                $grn->item_code,
                $grn->item_name,
                $grn->processed_at ? Carbon::parse($grn->processed_at)->format('d M Y') : '',
                $grn->reference_number_1 ?? '',
                'RETURN GRN',
                $grn->unit ?? '',
                $grn->grn_quantity,
                number_format($grn->total_price, 2),
                '',   // Qty Out
                '',   // Cost Out
                $grn->reference_number_2 ?? '',
                '',   // Vendor No
                '',   // Vendor Name
                $grn->return?->requisition?->department?->name ?? '',
                '',
                '',
            ];
        }

        // Sort by item code then date
        usort($rows, fn($a, $b) => $a[0] <=> $b[0] ?: $a[2] <=> $b[2]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Item Name',
            'Date',
            'Document No',
            'Type',
            'Unit',
            'Qty In',
            'Cost In',
            'Qty Out',
            'Cost Out',
            'Invoice No',
            'Vendor No',
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
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '343A40'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, 'B' => 35, 'C' => 14, 'D' => 20, 'E' => 16,
            'F' => 8,  'G' => 10, 'H' => 14, 'I' => 10, 'J' => 14,
            'K' => 18, 'L' => 14, 'M' => 25, 'N' => 25, 'O' => 25, 'P' => 35,
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
                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter('A1:P1');
            },
        ];
    }
}
