<?php

namespace Tests\Unit;

use App\Services\ProjectReportPeriodGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProjectReportPeriodGeneratorTest extends TestCase
{
    private $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ProjectReportPeriodGenerator();
    }

    public function test_calendar_month_uses_first_and_last_day_of_each_month()
    {
        $periods = $this->generator->generate('2026-01-08', 2, ProjectReportPeriodGenerator::CALENDAR_MONTH);

        $this->assertSame(['start' => '2026-01-01', 'end' => '2026-01-31'], $periods[1]);
        $this->assertSame(['start' => '2026-02-01', 'end' => '2026-02-28'], $periods[2]);
    }

    public function test_monthly_period_uses_maintenance_anniversary_without_gaps()
    {
        $periods = $this->generator->generate('2026-01-08', 2, ProjectReportPeriodGenerator::MAINTENANCE_MONTHLY);

        $this->assertSame(['start' => '2026-01-08', 'end' => '2026-02-07'], $periods[1]);
        $this->assertSame(['start' => '2026-02-08', 'end' => '2026-03-07'], $periods[2]);
    }

    #[DataProvider('multiMonthPatterns')]
    public function test_multi_month_patterns_follow_the_maintenance_anchor($pattern, $expectedEnd)
    {
        $period = $this->generator->generate('2026-01-08', 1, $pattern)[1];

        $this->assertSame('2026-01-08', $period['start']);
        $this->assertSame($expectedEnd, $period['end']);
    }

    public static function multiMonthPatterns()
    {
        return [
            'three months' => [ProjectReportPeriodGenerator::MAINTENANCE_3_MONTHS, '2026-04-07'],
            'four months' => [ProjectReportPeriodGenerator::MAINTENANCE_4_MONTHS, '2026-05-07'],
            'six months' => [ProjectReportPeriodGenerator::MAINTENANCE_6_MONTHS, '2026-07-07'],
        ];
    }

    public function test_end_of_month_anchor_does_not_overflow_into_another_month()
    {
        $periods = $this->generator->generate('2026-01-31', 2, ProjectReportPeriodGenerator::MAINTENANCE_MONTHLY);

        $this->assertSame(['start' => '2026-01-31', 'end' => '2026-02-27'], $periods[1]);
        $this->assertSame(['start' => '2026-02-28', 'end' => '2026-03-30'], $periods[2]);
    }

    public function test_manual_pattern_cannot_be_generated()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->generator->generate('2026-01-08', 1, ProjectReportPeriodGenerator::MANUAL);
    }
}
