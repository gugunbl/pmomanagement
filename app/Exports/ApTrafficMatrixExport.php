<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ApTrafficMatrixExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $lokasi;
    protected $dateColumns;
    protected $floors;
    protected $matrix;

    public function __construct($lokasi, $dateColumns, $floors, $matrix)
    {
        $this->lokasi = $lokasi;
        $this->dateColumns = $dateColumns;
        $this->floors = $floors;
        $this->matrix = $matrix;
    }

    public function view(): View
    {
        return view('traffic_ap.exports.matrix_excel', [
            'lokasi' => $this->lokasi,
            'dateColumns' => $this->dateColumns,
            'floors' => $this->floors,
            'matrix' => $this->matrix,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $totalCols = count($this->dateColumns) + 1; // 1 for Lantai
        $totalRows = count($this->floors) + 1; // 1 for Header

        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // Header style (Yellow background, bold, centered)
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'FFF200', // Yellow as in screenshot
                ],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // First column (LANTAI) - Light blue background
        $sheet->getStyle("A1:A{$totalRows}")->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => '8EA9DB', // Soft blue as in screenshot
                ],
            ],
        ]);

        // All data cells border and alignment
        $sheet->getStyle("A1:{$lastColLetter}{$totalRows}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'B0B0B0'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Center all data values
        $sheet->getStyle("B2:{$lastColLetter}{$totalRows}")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        return [];
    }
}
