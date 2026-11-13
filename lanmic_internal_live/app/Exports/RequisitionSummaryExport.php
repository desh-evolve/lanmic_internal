<?php
// app/Exports/RequisitionSummaryExport.php

namespace App\Exports;

use App\Models\Requisition;
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

class RequisitionSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Requisition::with(['user', 'department', 'items', 'approvedBy'])
            ->where('status', 'active');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['approve_status'])) {
            $query->where('approve_status', $this->filters['approve_status']);
        }
        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Requisition No',
            'Date',
            'User',
            'Department',
            'Items Count',
            'Total Value',
            'Status',
            'Approved By',
            'Approved Date',
        ];
    }

    public function map($requisition): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $requisition->requisition_no ?? 'REQ-' . str_pad($requisition->id, 6, '0', STR_PAD_LEFT),
            $requisition->created_at->format('d M Y'),
            $requisition->user->name ?? 'N/A',
            $requisition->department->name ?? 'N/A',
            $requisition->items->count(),
            number_format($requisition->items->sum('total_price'), 2),
            ucfirst($requisition->approve_status),
            $requisition->approvedBy->name ?? '-',
            $requisition->approved_at ? Carbon::parse($requisition->approved_at)->format('d M Y') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
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
            'D' => 25,
            'E' => 25,
            'F' => 12,
            'G' => 15,
            'H' => 12,
            'I' => 20,
            'J' => 15,
        ];
    }

    public function title(): string
    {
        return 'Requisition Summary';
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