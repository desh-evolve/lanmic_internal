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
        $query = RequisitionIssuedItem::with(['requisition.user', 'requisitionItem', 'issuedBy'])
            ->where('status', '!=', 'delete');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('issued_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('issued_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['item_code'])) {
            $query->where('item_code', 'like', '%' . $this->filters['item_code'] . '%');
        }

        return $query->orderBy('issued_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Issue Date',
            'Requisition No',
            'Item Code',
            'Item Name',
            'Issued To',
            'Issued Qty',
            'Unit Price',
            'Total Price',
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
            $item->requisition->requisition_no ?? 'REQ-' . str_pad($item->requisition_id, 6, '0', STR_PAD_LEFT),
            $item->item_code,
            $item->item_name,
            $item->requisition->user->name ?? 'N/A',
            $item->issued_quantity,
            number_format($item->unit_price, 2),
            number_format($item->total_price, 2),
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
            'A' => 8,
            'B' => 20,
            'C' => 20,
            'D' => 15,
            'E' => 30,
            'F' => 25,
            'G' => 12,
            'H' => 12,
            'I' => 15,
            'J' => 20,
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
                $event->sheet->setAutoFilter('A1:J1');
            },
        ];
    }
}