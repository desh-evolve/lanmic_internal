<?php
// app/Exports/MonthlySummaryExport.php

namespace App\Exports;

use App\Models\Requisition;
use App\Models\ReturnModel;
use App\Models\RequisitionIssuedItem;
use App\Models\GrnItem;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class MonthlySummaryExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $year;
    protected $departmentId;
    protected $subDeptId;

    public function __construct($year = null, $departmentId = null, $subDeptId = null)
    {
        $this->year         = $year ?? date('Y');
        $this->departmentId = $departmentId;
        $this->subDeptId    = $subDeptId;
    }

    public function array(): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($this->year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($this->year, $month, 1)->endOfMonth();

            $requisitions = Requisition::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'active');
            if ($this->departmentId) $requisitions->where('department_id', $this->departmentId);
            if ($this->subDeptId)    $requisitions->where('sub_department_id', $this->subDeptId);

            $reqIds = (clone $requisitions)->pluck('id');

            $returns = ReturnModel::whereBetween('returned_at', [$startDate, $endDate])
                ->where('status', '!=', 'delete');
            if ($reqIds->isNotEmpty()) {
                $returns->whereIn('requisition_id', $reqIds);
            } elseif ($this->departmentId || $this->subDeptId) {
                $returns->whereRaw('0=1');
            }

            $issuedQuery = RequisitionIssuedItem::whereBetween('issued_at', [$startDate, $endDate])
                ->where('status', '!=', 'delete');
            if ($reqIds->isNotEmpty()) {
                $issuedQuery->whereIn('requisition_id', $reqIds);
            } elseif ($this->departmentId || $this->subDeptId) {
                $issuedQuery->whereRaw('0=1');
            }

            $requisitionsCount = $requisitions->count();
            $approvedCount     = (clone $requisitions)->where('approve_status', 'approved')->count();
            $approvalRate      = $requisitionsCount > 0 ? round(($approvedCount / $requisitionsCount) * 100) . '%' : '0%';

            $data[] = [
                $startDate->format('F'),
                $requisitionsCount,
                $approvedCount,
                $approvalRate,
                $returns->count(),
                (clone $returns)->where('status', 'cleared')->count(),
                (clone $issuedQuery)->sum('issued_quantity'),
                number_format((clone $issuedQuery)->sum('total_price'), 2),
                GrnItem::whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', '!=', 'delete')->sum('grn_quantity'),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Month',
            'Requisitions',
            'Approved',
            'Approval Rate',
            'Returns',
            'Cleared',
            'Items Issued',
            'Total Cost',
            'GRN Items',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FD7E14']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 12,
            'D' => 15,
            'E' => 12,
            'F' => 12,
            'G' => 15,
            'H' => 15,
            'I' => 12,
        ];
    }

    public function title(): string
    {
        return 'Monthly Summary ' . $this->year;
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