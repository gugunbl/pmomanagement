<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerangkatTemplateExport implements FromArray, WithHeadings, WithTitle
{
    public function array(): array
    {
        // Return an empty array or a sample row
        return [
            [
                '[SAMPLE - AKAN DI-SKIP] SN12345678',
                'SIK-999',
                'SRV-HOST-01',
                'GTI - Ragunan',
                'RCK-01-A',
                '4',
                'aktif',
                '2024-01-01',
                '2027-01-01',
                '2024-01-15',
                'Enterprise'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Serial Number',
            'SIK',
            'Hostname',
            'Site / Lokasi (Nama Lokasi)',
            'Koordinat',
            'Lantai/Rak',
            'Status (aktif/tidak aktif/gu)',
            'Warranty Start (YYYY-MM-DD)',
            'Warranty End (YYYY-MM-DD)',
            'Tanggal Online (YYYY-MM-DD)',
            'License'
        ];
    }

    public function title(): string
    {
        return 'Template Import Perangkat';
    }
}
