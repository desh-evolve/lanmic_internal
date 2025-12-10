<?php
// app/Exports/UserActivityExport.php

namespace App\Exports;

use App\Models\User;
use App\Models\Requisition;
use App\Models\ReturnModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserActivityExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function array(): array
    {
        $users = User::with('roles')->get();
        $data = [];
        $index = 0;

        foreach ($users as $user) {
            $requisitions = Requisition::where('user_id', $user->id)
                ->where('status', 'active');

            $returns = ReturnModel::where('returned_by', $user->id)
                ->where('status', '!=', 'delete');

            if (!empty($this->filters['date_from'])) {
                $requisitions->whereDate('created_at', '>=', $this->filters['date_from']);
                $returns->whereDate('returned_at', '>=', $this->filters['date_from']);
            }
            if (!empty($this->filters['date_to'])) {
                $requisitions->whereDate('created_at', '<=', $this->filters['date_to']);
                $returns->whereDate('returned_at', '<=', $this->filters['date_to']);
            }

            $index++;
            $data[] = [
                $index,
                $user->name,
                $user->email,
                $user->roles->pluck('name')->implode(', ') ?: 'No role',
                $requisitions->count(),
                (clone $requisitions)->where('approve_status', 'pending')->count(),
                (clone $requisitions)->where('approve_status', 'approved')->count(),
                $returns->count(),
                (clone $returns)->where('status', 'pending')->count(),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            '#',
            'User',
            'Email',
            'Role(s)',
            'Total Requisitions',
            'Pending Req.',
            'Approved Req.',
            'Total Returns',
            'Pending Returns',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '20C997']
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 30,
            'D' => 20,
            'E' => 18,
            'F' => 14,
            'G' => 14,
            'H' => 15,
            'I' => 15,
        ];
    }

    public function title(): string
    {
        return 'User Activity';
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