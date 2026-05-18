<?php
// app/Exports/ScrapExport.php

namespace App\Exports;

use App\Models\ScrapItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Str;

class ScrapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = ScrapItem::with(['return.returnedBy', 'returnItem'])
            ->where('status', '!=', 'delete');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['item_code'])) {
            $query->where('item_code', 'like', '%' . $this->filters['item_code'] . '%');
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Date',
            'Return No',
            'Item Code',
            'Item Name',
            'Returned By',
            'Scrap Qty',
            'Unit Price',
            'Total Value',
            'Scrap Reason',
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $item->created_at->format('d M Y'),
            $item->return ? ($item->return->return_no ?? 'RET-' . str_pad($item->return_id, 6, '0', STR_PAD_LEFT)) : 'N/A',
            $item->item_code,
            $item->item_name,
            $item->return->returnedBy->name ?? 'N/A',
            $item->scrap_quantity,
            number_format($item->unit_price, 2),
            number_format($item->total_price, 2),
            Str::limit($item->scrap_reason ?? $item->remarks, 30),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '343A40']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 15,
            'C' => 20,
            'D' => 15,
            'E' => 30,
            'F' => 25,
            'G' => 12,
            'H' => 12,
            'I' => 15,
            'J' => 30,
        ];
    }

    public function title(): string
    {
        return 'Scrap Report';
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