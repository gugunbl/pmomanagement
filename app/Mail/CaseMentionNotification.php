<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaseMentionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $case;
    public $comment;
    public $mentionedUser;
    public $caseUrl;

    public function __construct($case, $comment, $mentionedUser, $caseUrl)
    {
        $this->case = $case;
        $this->comment = $comment;
        $this->mentionedUser = $mentionedUser;
        $this->caseUrl = $caseUrl;
    }

    public function build()
    {
        return $this->subject('Anda dimention pada Case #' . $this->case->id_case)
            ->view('emails.cases.mention');
    }
}
