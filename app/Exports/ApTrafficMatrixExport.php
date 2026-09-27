<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ApTrafficMatrixExport implements WithMultipleSheets
{
    protected $lokasi;
    protected $dateColumns;
    protected $floors;
    protected $matrixTraffic;
    protected $dailyTotalsTraffic;
    protected $matrixUser;
    protected $dailyTotalsUser;

    public function __construct($lokasi, $dateColumns, $floors, $matrixTraffic, $dailyTotalsTraffic = [], $matrixUser = [], $dailyTotalsUser = [])
    {
        $this->lokasi = $lokasi;
        $this->dateColumns = $dateColumns;
        $this->floors = $floors;
        $this->matrixTraffic = $matrixTraffic;
        $this->dailyTotalsTraffic = $dailyTotalsTraffic;
        $this->matrixUser = $matrixUser;
        $this->dailyTotalsUser = $dailyTotalsUser;
    }

    public function sheets(): array
    {
        return [
            new ApTrafficSheetExport('Traffic', $this->lokasi, $this->dateColumns, $this->floors, $this->matrixTraffic, $this->dailyTotalsTraffic),
            new ApTrafficSheetExport('Jumlah User', $this->lokasi, $this->dateColumns, $this->floors, $this->matrixUser, $this->dailyTotalsUser),
        ];
    }
}
