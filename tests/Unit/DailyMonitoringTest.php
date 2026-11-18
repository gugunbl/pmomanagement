<?php

namespace Tests\Unit;

use App\DailyMonitoring;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyMonitoringTest extends TestCase
{
    public function test_report_datetime_is_cast_to_a_datetime_instance(): void
    {
        $monitoring = new DailyMonitoring();
        $monitoring->setRawAttributes([
            'report_datetime' => '2026-11-18 08:15:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $monitoring->report_datetime);
        $this->assertSame('18-11-2026 08:15', $monitoring->report_datetime->format('d-m-Y H:i'));
    }
}
