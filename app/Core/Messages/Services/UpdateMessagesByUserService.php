<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\Message;

/**
 * Class UpdateMessagesByUserService
 * @package App\Services
 */
class UpdateMessagesByUserService
{

    private $validateMessagesByUserService;

    public function __construct(ValidateMessagesByUserService $validateMessagesByUserService) {
        $this->validateMessagesByUserService = $validateMessagesByUserService;
    }

    public function execute($messages, $userId)
    {

        Message::whereIn('message_id',$messages)
            ->update(['message_deleted'=>1]);
        return Message::whereIn('message_id',$messages)->paginateByRequest();

    }

}
