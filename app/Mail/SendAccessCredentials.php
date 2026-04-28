<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendAccessCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $defaultPassword;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $defaultPassword = 'kenangan')
    {
        $this->user = $user;
        $this->defaultPassword = $defaultPassword;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Akses System Ticketing')
                    ->markdown('emails.users.access-credentials');
    }
}
