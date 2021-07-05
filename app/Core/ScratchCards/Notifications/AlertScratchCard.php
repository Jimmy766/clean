<?php

    namespace App\Core\ScratchCards\Notifications;

    use App\Core\ScratchCards\Models\ScratchCard;
    use App\Core\Rapi\Models\Site;
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Queue\SerializesModels;

    class AlertScratchCard extends Mailable
    {
        use Queueable, SerializesModels;
        /**
         * @var \App\Core\ScratchCards\Models\ScratchCard
         */
        public $scratch_card;
        /**
         * @var \stdClass
         */
        public $error;
        /**
         * @var \App\Core\Rapi\Models\Site
         */
        public $site;
        /**
         * @var array
         */
        public $request_params;
        public $user;
        public $extra;

        /**
         * Create a new message instance.
         *
         * @return void
         */
        public function __construct(ScratchCard $scratch_card, \stdClass $error, Site $site, array $request_params, $user, $extra) {
            $this->scratch_card = $scratch_card;
            $this->error = $error;
            $this->site = $site;
            $this->request_params = $request_params;
            $this->user = $user;
            $this->extra = $extra;
        }

        /**
         * Build the message.
         *
         * @return $this
         */
        public function build() {
            $this->subject('Error Pariplay in '.$this->site->site_name);
            return $this->view('emails.alert_scratch_card');
        }
    }
