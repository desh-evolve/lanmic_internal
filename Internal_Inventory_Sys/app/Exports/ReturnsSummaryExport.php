<?php
// app/Exports/ReturnsSummaryExport.php

namespace App\Exports;

use App\Models\ReturnModel;
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
use Illuminate\Support\Str;

class ReturnsSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = ReturnModel::with(['returnedBy', 'items', 'processedBy'])
            ->where('status', '!=', 'delete');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('returned_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('returned_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['user_id'])) {
            $query->where('returned_by', $this->filters['user_id']);
        }

        return $query->orderBy('returned_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Return No',
            'Return Date',
            'Returned By',
            'Items Count',
            'Reason',
            'Status',
            'Processed By',
            'Processed Date',
        ];
    }

    public function map($return): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $return->return_no ?? 'RET-' . str_pad($return->id, 6, '0', STR_PAD_LEFT),
            Carbon::parse($return->returned_at)->format('d M Y H:i'),
            $return->returnedBy->name ?? 'N/A',
            $return->items->count(),
            Str::limit($return->reason, 50),
            ucfirst($return->status),
            $return->processedBy->name ?? '-',
            $return->processed_at ? Carbon::parse($return->processed_at)->format('d M Y') : '-',
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
            'D' => 25,
            'E' => 12,
            'F' => 40,
            'G' => 12,
            'H' => 20,
            'I' => 15,
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
                $event->sheet->setAutoFilter('A1:I1');
            },
        ];
    }
}