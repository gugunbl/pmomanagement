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
                'SN12345678',
                'RCK-01-A',
                '4',
                'New Device',
                'Enterprise',
                '2024-01-01',
                '2027-01-01',
                '2024-01-15'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Serial Number',
            'Koordinat',
            'Lantai/Rak',
            'Status Server (New Device / Used Device)',
            'License',
            'Warranty Start (YYYY-MM-DD)',
            'Warranty End (YYYY-MM-DD)',
            'Tanggal Online (YYYY-MM-DD)'
        ];
    }

    public function title(): string
    {
        return 'Template Import Perangkat';
    }
}
