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
    public function __construct($user)
    {
        $this->user = $user;
        try {
            $this->defaultPassword = \Illuminate\Support\Facades\Crypt::decryptString($user->password);
        } catch (\Exception $e) {
            $this->defaultPassword = '(Password tidak dapat didekripsi, kemungkinan masih menggunakan hash lama. Silakan reset password terlebih dahulu.)';
        }
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
