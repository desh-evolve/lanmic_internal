<?php
// app/Exports/IssuedItemsExport.php

namespace App\Exports;

use App\Models\RequisitionIssuedItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class IssuedItemsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = RequisitionIssuedItem::with(['requisition.user', 'requisition.department', 'requisition.subDepartment', 'requisitionItem', 'issuedBy'])
            ->join('requisitions as r_sort', 'requisition_issued_items.requisition_id', '=', 'r_sort.id')
            ->join('departments as d_sort', 'r_sort.department_id', '=', 'd_sort.id')
            ->select('requisition_issued_items.*')
            ->where('requisition_issued_items.status', '!=', 'delete');

        if (!empty($this->filters['date_from']))     $query->whereDate('requisition_issued_items.issued_at', '>=', $this->filters['date_from']);
        if (!empty($this->filters['date_to']))       $query->whereDate('requisition_issued_items.issued_at', '<=', $this->filters['date_to']);
        if (!empty($this->filters['item_code']))     $query->where('requisition_issued_items.item_code', 'like', '%' . $this->filters['item_code'] . '%');
        if (!empty($this->filters['item_name']))     $query->where('requisition_issued_items.item_name', 'like', '%' . $this->filters['item_name'] . '%');
        if (!empty($this->filters['department_id'])) $query->where('r_sort.department_id', $this->filters['department_id']);
        if (!empty($this->filters['category']))      $query->where('requisition_issued_items.item_category', 'like', '%' . $this->filters['category'] . '%');

        return $query
            ->orderBy('d_sort.name', 'asc')
            ->orderBy('requisition_issued_items.issued_at', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Issue Date',
            'Requisition No',
            'Item Code',
            'Item Name',
            'UOM',
            'Issued Qty',
            'Unit Price',
            'Total Price',
            'Department',
            'Sub-Department',
            'Remarks',
            'Issued To',
            'Issued By',
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            Carbon::parse($item->issued_at)->format('d M Y H:i'),
            $item->requisition->requisition_number ?? 'REQ-' . str_pad($item->requisition_id, 6, '0', STR_PAD_LEFT),
            $item->item_code,
            $item->item_name,
            $item->unit ?? '',
            $item->issued_quantity,
            number_format($item->unit_price, 2),
            number_format($item->total_price, 2),
            $item->requisition->department->name ?? '',
            $item->requisition->subDepartment->name ?? '',
            $item->notes ?? '',
            $item->requisition->user->name ?? 'N/A',
            $item->issuedBy->name ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '17A2B8']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 20,
            'C' => 18,
            'D' => 14,
            'E' => 30,
            'F' => 8,
            'G' => 10,
            'H' => 12,
            'I' => 14,
            'J' => 25,
            'K' => 22,
            'L' => 30,
            'M' => 25,
            'N' => 20,
        ];
    }

    public function title(): string
    {
        return 'Issued Items';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter('A1:N1');
            },
        ];
    }
}