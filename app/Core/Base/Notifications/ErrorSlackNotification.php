<?php

namespace App\Core\Base\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class ErrorSlackNotification extends Notification
{
    use Queueable;

    private $data;

    /**
     * Create a new notification instance.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $data = $this->data;
        $message = '';
        $environment = array_key_exists('environment', $data) ? $data['environment'] : 'empty environment';
        $countError = array_key_exists('count_errors', $data) ? $data['count_errors'] : 0;
        $messageError = array_key_exists('message_error', $data) ? $data['message_error'] : '';
        $title = "ENV:$environment :eyes: ERRORS:{$countError} :fire: {$messageError}";
        return (new SlackMessage)
            ->success()
            ->content($title)
            ->attachment(function ($attachment) use ($message, $data) {
                $attachment->title($message, $data)->fields($data);
            });
    }
}
