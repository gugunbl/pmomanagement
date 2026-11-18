<?php

namespace App\Console\Commands;

use App\ProjectReport;
use App\ProjectReportNotification;
use App\Services\ProjectReportPeriodReminder as ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProjectReportPeriodReminder extends Command
{
    protected $signature = 'reports:period-end-reminder
        {--date= : Simulasikan tanggal trigger YYYY-MM-DD}
        {--report= : Jalankan untuk satu ID laporan}
        {--dry-run : Tampilkan kandidat tanpa mengirim email}
        {--force : Kirim ulang meskipun sebelumnya sudah terkirim}';

    protected $description = 'Kirim reminder ketika laporan memasuki tanggal akhir periode';

    public function handle(ReminderService $reminder)
    {
        if (!setting('report_period_reminder_enabled', 1) && !$this->option('force')) {
            $this->warn('Reminder periode laporan sedang dinonaktifkan di Pengaturan.');
            return 0;
        }

        try {
            $date = Carbon::parse($this->option('date') ?: now()->toDateString())->startOfDay();
        } catch (\Throwable $e) {
            $this->error('Format --date tidak valid. Gunakan YYYY-MM-DD.');
            return 1;
        }

        $query = $this->eligibleReportsQuery();

        if ($this->option('report')) {
            $query->whereKey((int) $this->option('report'));
        } else {
            $query->whereDate('period_end', $date->toDateString());
        }

        $reports = $query->get();

        if (!$this->option('report') && !$this->option('dry-run')) {
            $retryIds = ProjectReportNotification::where('notification_type', 'period_end')
                ->where('status', 'failed')
                ->where('attempts', '<', 3)
                ->whereDate('trigger_date', '>=', $date->copy()->subDays(7)->toDateString())
                ->whereDate('trigger_date', '<', $date->toDateString())
                ->pluck('project_report_id');

            if ($retryIds->isNotEmpty()) {
                $retryReports = $this->eligibleReportsQuery()
                    ->whereIn('id', $retryIds)
                    ->get();
                $reports = $reports->concat($retryReports)->unique('id')->values();
            }
        }

        if ($reports->isEmpty()) {
            $this->info('Tidak ada laporan yang perlu dikirim reminder.');
            return 0;
        }

        $this->table(['ID', 'Project', 'Laporan', 'Periode selesai', 'PM'], $reports->map(function ($report) {
            return [
                $report->id,
                optional($report->project)->nama_project,
                '#' . $report->report_ke,
                optional($report->period_end)->format('Y-m-d'),
                optional(optional($report->project)->mPMMaintenance)->nama_pm ?: '-',
            ];
        })->all());

        if ($this->option('dry-run')) {
            $this->info('Dry-run selesai. Tidak ada email atau log pengiriman yang dibuat.');
            return 0;
        }

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($reports as $report) {
            $notification = $reminder->send($report, (bool) $this->option('force'));
            if ($notification->status === 'sent') {
                $sent++;
            } elseif ($notification->status === 'failed') {
                $failed++;
                $this->warn('Gagal: laporan ' . $report->id . ' - ' . $notification->error_message);
            } else {
                $skipped++;
            }
        }

        $this->info("Selesai. Terkirim: {$sent}, gagal: {$failed}, dilewati: {$skipped}.");
        return $failed > 0 ? 1 : 0;
    }

    private function eligibleReportsQuery()
    {
        return ProjectReport::with(['project.mPMMaintenance.user'])
            ->whereNotNull('period_start')
            ->whereNotNull('period_end')
            ->where('status', '<>', 'signoff')
            ->where(function ($builder) {
                $builder->whereNull('uploaded_file')->orWhere('uploaded_file', '');
            })
            ->whereHas('project', function ($projectQuery) {
                $projectQuery->where('status_maintenance', 1);
            });
    }
}
