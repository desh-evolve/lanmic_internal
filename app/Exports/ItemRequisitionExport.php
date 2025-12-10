<?php
// app/Exports/ItemRequisitionExport.php

namespace App\Exports;

use App\Models\RequisitionItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemRequisitionExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = RequisitionItem::with(['requisition.user', 'requisition.department'])
            ->where('status', '!=', 'delete');

        if (!empty($this->filters['date_from'])) {
            $query->whereHas('requisition', function($q) {
                $q->whereDate('created_at', '>=', $this->filters['date_from']);
            });
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereHas('requisition', function($q) {
                $q->whereDate('created_at', '<=', $this->filters['date_to']);
            });
        }
        if (!empty($this->filters['item_code'])) {
            $query->where('item_code', 'like', '%' . $this->filters['item_code'] . '%');
        }
        if (!empty($this->filters['item_name'])) {
            $query->where('item_name', 'like', '%' . $this->filters['item_name'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Requisition No',
            'Date',
            'Item Code',
            'Item Name',
            'Requested By',
            'Department',
            'Quantity',
            'Unit Price',
            'Total Price',
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $item->requisition->requisition_no ?? 'REQ-' . str_pad($item->requisition_id, 6, '0', STR_PAD_LEFT),
            $item->created_at->format('d M Y'),
            $item->item_code,
            $item->item_name,
            $item->requisition->user->name ?? 'N/A',
            $item->requisition->department->name ?? 'N/A',
            $item->quantity,
            number_format($item->unit_price, 2),
            number_format($item->total_price, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28A745']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 20,
            'C' => 15,
            'D' => 15,
            'E' => 30,
            'F' => 25,
            'G' => 25,
            'H' => 12,
            'I' => 12,
            'J' => 15,
        ];
    }

    public function title(): string
    {
        return 'Item Requisition';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter('A1:J1');
            },
        ];
    }
}