<?php
// app/Exports/DepartmentActivityExport.php

namespace App\Exports;

use App\Models\Department;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartmentActivityExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function array(): array
    {
        $departments = Department::active()->get();
        $data = [];
        $index = 0;

        foreach ($departments as $department) {
            $requisitions = Requisition::where('department_id', $department->id)
                ->where('status', 'active');

            if (!empty($this->filters['date_from'])) {
                $requisitions->whereDate('created_at', '>=', $this->filters['date_from']);
            }
            if (!empty($this->filters['date_to'])) {
                $requisitions->whereDate('created_at', '<=', $this->filters['date_to']);
            }

            $requisitionIds = $requisitions->pluck('id');
            $totalRequisitions = $requisitions->count();
            
            $index++;
            $data[] = [
                $index,
                $department->name,
                $totalRequisitions,
                Requisition::where('department_id', $department->id)->where('status', 'active')->where('approve_status', 'pending')->count(),
                Requisition::where('department_id', $department->id)->where('status', 'active')->where('approve_status', 'approved')->count(),
                Requisition::where('department_id', $department->id)->where('status', 'active')->where('approve_status', 'rejected')->count(),
                RequisitionItem::whereIn('requisition_id', $requisitionIds)->sum('quantity'),
                number_format(RequisitionItem::whereIn('requisition_id', $requisitionIds)->sum('total_price'), 2),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            '#',
            'Department',
            'Total Requisitions',
            'Pending',
            'Approved',
            'Rejected',
            'Total Items',
            'Total Value',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '6F42C1']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 30,
            'C' => 18,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 15,
            'H' => 18,
        ];
    }

    public function title(): string
    {
        return 'Department Activity';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->freezePane('A2');
                $event->sheet->setAutoFilter('A1:H1');
            },
        ];
    }
}