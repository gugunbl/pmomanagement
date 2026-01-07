<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class MaintenanceReminder extends Command
{
    protected $signature = 'maintenance:reminder';
    protected $description = 'Send reminder email for maintenance project (expired & warning)';

    public function handle()
    {
        $today = Carbon::today();
        $warningLimit = $today->copy()->addMonths(5);

        // 🔴 EXPIRED
        $expired = DB::table('m_project as p')
            ->leftJoin('m_project_manager as pm', 'pm.id_pm', '=', 'p.pm_maintenance')
            ->where('p.status_maintenance', 1)
            ->whereDate('p.tanggal_akhir_maintenance', '<', $today)
            ->select('p.*', 'pm.nama_pm')
            ->get();

        // 🟡 WARNING
        $warning = DB::table('m_project as p')
            ->leftJoin('m_project_manager as pm', 'pm.id_pm', '=', 'p.pm_maintenance')
            ->where('p.status_maintenance', 1)
            ->whereDate('p.tanggal_akhir_maintenance', '>=', $today)
            ->whereDate('p.tanggal_akhir_maintenance', '<=', $warningLimit)
            ->select('p.*', 'pm.nama_pm')
            ->get();

        if ($expired->isEmpty() && $warning->isEmpty()) {
            $this->info('No reminder needed');
            return;
        }

        // Kirim email
        Mail::to(config('mail.reminder_to'))
            ->send(new \App\Mail\MaintenanceReminderMail($expired, $warning));

        $this->info('Maintenance reminder email sent');
    }
}
