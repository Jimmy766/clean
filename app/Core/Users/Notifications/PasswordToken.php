<?php

namespace App\Core\Users\Notifications;

use App\Core\Base\Services\LocationResolveLangService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;

class PasswordToken extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $client_url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $client_url)
    {
        $this->user = $user;
        $this->client_url = $client_url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $lang = $this->user->usr_language;
        $lang = LocationResolveLangService::execute($lang);
        /* es-la transform to es/la */
        App::setLocale(str_replace("-", "/", $lang));

        return $this->subject(trans("emails/forgot_password.email_subject_forgot_pass", ["site_name" => optional($this->user->site)->site_name]))
        ->view('emails.forgot_password');
    }
}
