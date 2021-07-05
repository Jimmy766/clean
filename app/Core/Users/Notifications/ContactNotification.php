<?php

namespace App\Core\Users\Notifications;

use App;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class NoteStoreNotification
 * @package App\Core\Notes\Notification
 */
class ContactNotification extends Notification implements ShouldQueue
{

    use Queueable;

    /**
     * @var
     */
    private $via;

    /**
     * @var int
     */
    private $user;
    /**
     * @var string
     */
    private $host;
    /**
     * @var array
     */
    private $dataRequest;

    /**
     * NoteStoreNotification constructor.
     * @param        $user
     * @param string $locale
     * @param $dataRequest
     * @param array  $via
     */
    public function __construct(string $locale, $dataRequest, array $via = [ 'mail' ])
    {
        App::setLocale($locale);
        $this->via  = $via;
        $this->dataRequest = $dataRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $data = [
            'dataRequest' => $this->dataRequest,
        ];
        return ( new MailMessage() )->subject(__('Contact to System'))
            ->markdown('emails.ContactEmailNotificationTemplate', $data);
    }
}

