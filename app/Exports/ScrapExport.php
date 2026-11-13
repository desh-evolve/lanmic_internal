<?php
// app/Exports/ScrapExport.php

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
use Illuminate\Support\Str;

/**
 * Return Reject Report export.
 *
 * Covers every ReturnItem denied by an approver — both items scrapped in full
 * (a ScrapItem record exists) and items rejected outright via the Reject
 * toggle (no ScrapItem, nothing posted anywhere). The ScrapItem, when present,
 * is the source of truth for quantity/price; otherwise falls back to the
 * ReturnItem's own quantity and the original issued unit price.
 */
class ScrapExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = ReturnItem::with(['return.returnedBy', 'issuedItem', 'scrapItem', 'approvedBy'])
            ->where('status', 'active')
            ->where('approve_status', 'rejected');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('approved_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('approved_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['item_code'])) {
            $query->where('item_code', 'like', '%' . $this->filters['item_code'] . '%');
        }

        return $query->orderBy('approved_at', 'desc')->get();
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
            'Type',
            'Quantity',
            'Unit Price',
            'Total Value',
            'Reason',
        ];
    }

    public function map($item): array
    {
        static $index = 0;
        $index++;

        $scrap = $item->scrapItem;
        $quantity = $scrap->scrap_quantity ?? $item->quantity;
        $unitPrice = $scrap->unit_price ?? ($item->issuedItem->unit_price ?? 0);
        $totalPrice = $scrap->total_price ?? ($unitPrice * $quantity);

        return [
            $index,
            $item->approved_at ? $item->approved_at->format('d M Y') : '',
            $item->return ? ('RET-' . str_pad($item->return_id, 6, '0', STR_PAD_LEFT)) : 'N/A',
            $item->item_code,
            $item->item_name,
            $item->return->returnedBy->name ?? 'N/A',
            $scrap ? 'Scrapped' : 'Rejected',
            $quantity,
            number_format($unitPrice, 2),
            number_format($totalPrice, 2),
            Str::limit($item->admin_note ?? '', 60),
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
            'A' => 6,
            'B' => 14,
            'C' => 18,
            'D' => 16,
            'E' => 30,
            'F' => 22,
            'G' => 12,
            'H' => 12,
            'I' => 12,
            'J' => 14,
            'K' => 40,
        ];
    }

    public function title(): string
    {
        return 'Return Reject Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter('A1:K1');
            },
        ];
    }
}
