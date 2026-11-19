<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

class ProjectReportPeriodGenerator
{
    const MANUAL = 'manual';
    const CALENDAR_MONTH = 'calendar_month';
    const MAINTENANCE_MONTHLY = 'maintenance_monthly';
    const MAINTENANCE_3_MONTHS = 'maintenance_3_months';
    const MAINTENANCE_4_MONTHS = 'maintenance_4_months';
    const MAINTENANCE_6_MONTHS = 'maintenance_6_months';

    public static function patterns()
    {
        return [
            self::MANUAL,
            self::CALENDAR_MONTH,
            self::MAINTENANCE_MONTHLY,
            self::MAINTENANCE_3_MONTHS,
            self::MAINTENANCE_4_MONTHS,
            self::MAINTENANCE_6_MONTHS,
        ];
    }

    public function generate($maintenanceStart, $total, $pattern)
    {
        $total = (int) $total;
        if ($total < 0 || $total > 60) {
            throw new InvalidArgumentException('Jumlah laporan harus berada di antara 0 sampai 60.');
        }

        if (!in_array($pattern, self::patterns(), true) || $pattern === self::MANUAL) {
            throw new InvalidArgumentException('Pola periode laporan tidak dapat digenerate.');
        }

        $anchor = CarbonImmutable::parse($maintenanceStart)->startOfDay();
        $periods = [];

        for ($index = 0; $index < $total; $index++) {
            if ($pattern === self::CALENDAR_MONTH) {
                $start = $anchor->startOfMonth()->addMonths($index);
                $end = $start->endOfMonth();
            } else {
                $months = $this->monthsForPattern($pattern);
                // Always calculate from the original anchor. This prevents date
                // drift for starts such as 29, 30, or 31 January.
                $start = $anchor->addMonthsNoOverflow($index * $months);
                $nextStart = $anchor->addMonthsNoOverflow(($index + 1) * $months);
                $end = $nextStart->subDay();
            }

            $periods[$index + 1] = [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ];
        }

        return $periods;
    }

    private function monthsForPattern($pattern)
    {
        $months = [
            self::MAINTENANCE_MONTHLY => 1,
            self::MAINTENANCE_3_MONTHS => 3,
            self::MAINTENANCE_4_MONTHS => 4,
            self::MAINTENANCE_6_MONTHS => 6,
        ];

        if (!isset($months[$pattern])) {
            throw new InvalidArgumentException('Pola periode laporan tidak didukung.');
        }

        return $months[$pattern];
    }
}
