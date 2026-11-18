<?php

namespace App\Mail;

use App\ProjectReport;
use Illuminate\Mail\Mailable;

class ProjectReportPeriodReminderMail extends Mailable
{
    public $report;
    public $monitoringUrl;
    public $bodyMessage;

    public function __construct(ProjectReport $report, $monitoringUrl, $bodyMessage)
    {
        $this->report = $report;
        $this->monitoringUrl = $monitoringUrl;
        $this->bodyMessage = $bodyMessage;
    }

    public function build()
    {
        $projectName = optional($this->report->project)->nama_project ?: 'Project';
        $template = setting(
            'report_period_reminder_subject',
            '[PMO] Periode Laporan #{report_ke} Memasuki Tanggal Akhir - {project}'
        );
        $subject = strtr($template, [
            '{report_ke}' => $this->report->report_ke,
            '{project}' => $projectName,
        ]);

        return $this->subject($subject)
            ->view('emails.project-report-period-reminder');
    }
}
