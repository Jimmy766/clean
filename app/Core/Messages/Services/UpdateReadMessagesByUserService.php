<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\Message;
use Carbon\Carbon;

/**
 * Class UpdateReadMessagesByUserService
 * @package App\Services
 */
class UpdateReadMessagesByUserService
{

    public function execute($messages)
    {

        Message::whereIn('message_id',$messages)
            ->update(['message_read'=>1,
                'message_date_read'=> Carbon::now()
            ]);
        return Message::whereIn('message_id',$messages)->paginateByRequest();

    }

}
