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
            ])
            ->where('status', 'active');

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
            'Return Date',
            'Return No',
            'Requisition No',
            'Item Code',
            'Item Name',
            'Department',
            'Sub-Department',
            'Qty',
            'Type',
            'Remarks',
            'Returned By',
            'Status',
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
            $ret?->requisition?->department?->name ?? '',
            $ret?->requisition?->subDepartment?->name ?? '',
            $item->quantity,
            $item->return_type === 'used' ? 'Used' : 'Same Condition',
            $item->notes ?? '',
            $ret?->returnedBy?->name ?? 'N/A',
            ucfirst($ret?->status ?? ''),
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
            'A' => 8,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 15,
            'F' => 30,
            'G' => 25,
            'H' => 25,
            'I' => 8,
            'J' => 18,
            'K' => 35,
            'L' => 25,
            'M' => 12,
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
                $event->sheet->setAutoFilter('A1:M1');
            },
        ];
    }
}