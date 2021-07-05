<?php

namespace App\Core\Rapi\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertMails extends Mailable
{
    use Queueable, SerializesModels;

    public $ip;
    public $endpoint;
    public $type;
    public $alert;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ip, $endpoint, $type, $alert) {
        $this->ip = $ip;
        $this->endpoint = $endpoint;
        $this->type = $type;
        $this->alert = $alert;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.alert_mails');
    }
}
