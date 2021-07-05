<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\Message;
use Exception;
use Illuminate\Http\Response;

/**
 * Class ValidateMessagesByUserService
 * @package App\Services
 */
class ValidateMessagesByUserService
{

    /**
     * @param \App\Core\Messages\Models\Message $message
     * @param         $userId
     * @throws Exception
     */
    public function execute(Message $message, $userId)
    {
        $oneMessage = Message::where('message_id', $message->message_id)
            ->where('usr_id', $userId)
            ->first();

        if ($oneMessage === null) {
            throw new Exception(__('error message dont is to user'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

}
