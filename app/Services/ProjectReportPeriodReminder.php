<?php

namespace App\Services;

use App\Mail\ProjectReportPeriodReminderMail;
use App\ProjectReport;
use App\ProjectReportNotification;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProjectReportPeriodReminder
{
    public function send(ProjectReport $report, $force = false)
    {
        $report->loadMissing(['project.mPMMaintenance.user']);
        $triggerDate = $report->period_end ? $report->period_end->toDateString() : null;

        if (!$triggerDate) {
            throw new \InvalidArgumentException('Periode selesai laporan belum diisi.');
        }

        $notification = ProjectReportNotification::where('project_report_id', $report->id)
            ->where('notification_type', 'period_end')
            ->whereDate('trigger_date', $triggerDate)
            ->first();

        if (!$notification) {
            try {
                $notification = ProjectReportNotification::create([
                    'project_report_id' => $report->id,
                    'notification_type' => 'period_end',
                    'trigger_date' => $triggerDate,
                    'status' => 'pending',
                    'attempts' => 0,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $notification = ProjectReportNotification::where('project_report_id', $report->id)
                    ->where('notification_type', 'period_end')
                    ->whereDate('trigger_date', $triggerDate)
                    ->firstOrFail();
            }
        }

        if (!$force && ($notification->status === 'sent' || $notification->attempts >= 3)) {
            return $notification;
        }

        [$to, $cc] = $this->recipients($report);
        $snapshot = ['to' => $to, 'cc' => $cc];

        if (empty($to)) {
            $notification->update([
                'recipient_snapshot' => $snapshot,
                'status' => 'failed',
                'attempts' => $notification->attempts + 1,
                'error_message' => 'Email PM Maintenance dan recipient fallback belum dikonfigurasi.',
            ]);

            return $notification->fresh();
        }

        $notification->update([
            'recipient_snapshot' => $snapshot,
            'status' => 'pending',
            'attempts' => $notification->attempts + 1,
            'error_message' => null,
        ]);

        $project = $report->project;
        $monitoringUrl = route('project.report.monitoring', [
            'customer' => $project->customer,
            'project' => $project->id_project,
        ]);
        $bodyMessage = setting(
            'report_period_reminder_body',
            'Periode laporan telah memasuki tanggal akhir hari ini. Mohon mengatur jadwal pembuatan laporan PM.'
        );

        try {
            $mail = Mail::to($to);
            if (!empty($cc)) {
                $mail->cc($cc);
            }
            $mail->send(new ProjectReportPeriodReminderMail($report, $monitoringUrl, $bodyMessage));

            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $notification->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            Log::error('Project report period reminder failed.', [
                'project_report_id' => $report->id,
                'notification_id' => $notification->id,
                'message' => $e->getMessage(),
            ]);
        }

        return $notification->fresh();
    }

    private function recipients(ProjectReport $report)
    {
        $fallback = $this->parseEmails(setting('report_period_reminder_to', ''));
        $pmEmail = optional(optional(optional($report->project)->mPMMaintenance)->user)->email;
        $pmEmail = filter_var($pmEmail, FILTER_VALIDATE_EMAIL) ? strtolower($pmEmail) : null;

        if ($pmEmail) {
            return [[$pmEmail], array_values(array_diff($fallback, [$pmEmail]))];
        }

        return [$fallback, []];
    }

    private function parseEmails($value)
    {
        return collect(preg_split('/[,;\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY))
            ->map(function ($email) {
                return strtolower(trim($email));
            })
            ->filter(function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            })
            ->unique()
            ->values()
            ->all();
    }
}
