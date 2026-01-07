<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class MaintenanceReminderMail extends Mailable
{
    public $expired;
    public $warning;

    public function __construct($expired, $warning)
    {
        $this->expired = $expired;
        $this->warning = $warning;
    }

    public function build()
    {
        return $this
            ->subject('Reminder Maintenance Project')
            ->view('emails.maintenance-reminder');
    }
}
