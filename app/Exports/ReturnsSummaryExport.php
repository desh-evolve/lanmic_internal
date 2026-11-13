<?php
// app/Exports/ReturnsSummaryExport.php

namespace App\Exports;

use App\Models\ReturnItem;
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

class ReturnsSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = ReturnItem::with([
                'return.returnedBy',
                'return.requisition.department',
                'return.requisition.subDepartment',
                'issuedItem',
                'approvedBy',
            ])
            ->where('status', 'active')
            // Rejected items are tracked separately in the Return Reject Report.
            ->where('approve_status', '!=', 'rejected');

        if (!empty($this->filters['date_from']))      $query->whereHas('return', fn($q) => $q->whereDate('returned_at', '>=', $this->filters['date_from']));
        if (!empty($this->filters['date_to']))        $query->whereHas('return', fn($q) => $q->whereDate('returned_at', '<=', $this->filters['date_to']));
        if (!empty($this->filters['status']))         $query->whereHas('return', fn($q) => $q->where('status', $this->filters['status']));
        if (!empty($this->filters['user_id']))        $query->whereHas('return', fn($q) => $q->where('returned_by', $this->filters['user_id']));
        if (!empty($this->filters['department_id']))  $query->whereHas('return.requisition', fn($q) => $q->where('department_id', $this->filters['department_id']));
        if (!empty($this->filters['item_name']))      $query->where('item_name', 'like', '%' . $this->filters['item_name'] . '%');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Returned Date',
            'Return Number',
            'Requisition Number',
            'Item Code',
            'Item Name',
            'UOM',
            'Qty',
            'Unit Price',
            'Total Price',
            'Department',
            'Sub-Department',
            'Remark',
            'Returned By',
            'Accepted By',
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;
        $ret = $item->return;

        return [
            $index,
            $ret ? Carbon::parse($ret->returned_at)->format('d M Y H:i') : '',
            $ret ? 'RET-' . str_pad($ret->id, 6, '0', STR_PAD_LEFT) : '',
            $ret?->requisition?->requisition_number ?? '',
            $item->item_code,
            $item->item_name,
            $item->unit ?? '',
            $item->quantity,
            $item->issuedItem ? number_format($item->issuedItem->unit_price, 2) : '',
            $item->issuedItem ? number_format($item->issuedItem->unit_price * $item->quantity, 2) : '',
            $ret?->requisition?->department?->name ?? '',
            $ret?->requisition?->subDepartment?->name ?? '',
            $item->admin_note ?? '',
            $ret?->returnedBy?->name ?? 'N/A',
            $item->approvedBy?->name ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC3545']
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
            'D' => 18,
            'E' => 14,
            'F' => 30,
            'G' => 8,
            'H' => 10,
            'I' => 12,
            'J' => 14,
            'K' => 25,
            'L' => 22,
            'M' => 30,
            'N' => 25,
            'O' => 25,
        ];
    }

    public function title(): string
    {
        return 'Returns Summary';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter('A1:O1');
            },
        ];
    }
}