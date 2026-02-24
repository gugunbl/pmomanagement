<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class MonitoringBAExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $listBA;

    public function __construct($listBA)
    {
        $this->listBA = $listBA;
    }

    public function collection()
    {
        return $this->listBA;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Project',
            'Nomor SPK',
            'PM Maintenance',
            'TOP Ke',
            'Start Date BA',
            'End Date BA',
            'Status',
            'Keterangan',
        ];
    }

    public function map($ba): array
    {
        static $no = 1;
        return [
            $no++,
            $ba->project->nama_project ?? '-',
            $ba->project->nomor_spk ?? '-',
            $ba->project->mPMMaintenance->nama_pm ?? '-',
            $ba->top_ke,
            $ba->periode_start ? Carbon::parse($ba->periode_start)->format('d M Y') : '-',
            $ba->periode_end ? Carbon::parse($ba->periode_end)->format('d M Y') : '-',
            strtoupper($ba->status),
            $ba->keterangan ?? '-',
        ];
    }
}
