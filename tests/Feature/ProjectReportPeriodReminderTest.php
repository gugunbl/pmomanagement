<?php

namespace Tests\Feature;

use App\Mail\ProjectReportPeriodReminderMail;
use App\ProjectReport;
use App\Services\ProjectReportPeriodReminder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProjectReportPeriodReminderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->text('value');
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
        });
        Schema::create('m_project_manager', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pm')->primary();
            $table->string('nama_pm');
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('user_id')->nullable();
        });
        Schema::create('m_project', function (Blueprint $table) {
            $table->unsignedBigInteger('id_project')->primary();
            $table->string('nama_project');
            $table->string('customer')->nullable();
            $table->boolean('status_maintenance')->default(true);
            $table->unsignedBigInteger('pm_maintenance')->nullable();
        });
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->integer('report_ke');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('submission_date')->nullable();
            $table->string('status')->default('open');
            $table->text('uploaded_file')->nullable();
            $table->timestamps();
        });
        Schema::create('project_report_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_report_id');
            $table->string('notification_type');
            $table->date('trigger_date');
            $table->text('recipient_snapshot')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['project_report_id', 'notification_type', 'trigger_date']);
        });

        DB::table('users')->insert(['id' => 7, 'name' => 'PM User', 'email' => 'pm@example.com']);
        DB::table('m_project_manager')->insert(['id_pm' => 4, 'nama_pm' => 'PM User', 'status' => 1, 'user_id' => 7]);
        DB::table('m_project')->insert([
            'id_project' => 10,
            'nama_project' => 'Project Reminder',
            'customer' => 'Customer A',
            'status_maintenance' => 1,
            'pm_maintenance' => 4,
        ]);
        DB::table('project_reports')->insert([
            'id' => 20,
            'project_id' => 10,
            'report_ke' => 2,
            'period_start' => '2026-11-01',
            'period_end' => '2026-11-30',
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_reminder_is_sent_to_pm_and_is_idempotent()
    {
        Mail::fake();
        $report = ProjectReport::findOrFail(20);
        $service = app(ProjectReportPeriodReminder::class);

        $first = $service->send($report);
        $second = $service->send($report->fresh());

        $this->assertSame('sent', $first->status);
        $this->assertSame('sent', $second->status);
        $this->assertSame(1, $second->attempts);
        $this->assertDatabaseCount('project_report_notifications', 1);
        Mail::assertSent(ProjectReportPeriodReminderMail::class, function ($mail) {
            return $mail->hasTo('pm@example.com');
        });
        Mail::assertSentCount(1);
    }

    public function test_missing_recipient_is_recorded_as_failed()
    {
        DB::table('users')->where('id', 7)->update(['email' => null]);

        $notification = app(ProjectReportPeriodReminder::class)
            ->send(ProjectReport::findOrFail(20));

        $this->assertSame('failed', $notification->status);
        $this->assertSame(1, $notification->attempts);
        $this->assertStringContainsString('belum dikonfigurasi', $notification->error_message);
    }

    public function test_dry_run_lists_due_report_without_sending_or_logging()
    {
        Mail::fake();

        $this->artisan('reports:period-end-reminder', [
            '--date' => '2026-11-30',
            '--dry-run' => true,
        ])->assertExitCode(0);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('project_report_notifications', 0);
    }
}
