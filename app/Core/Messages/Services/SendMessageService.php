<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\MessageBatch;
use Carbon\Carbon;

/**
 * Class SendMessageService
 * @package App\Services
 */
class SendMessageService
{


    public function execute($request)
    {
        $messageBatch=new MessageBatch();

        $messageBatch->csv_file=file_get_contents($request->csv_file);
        $messageBatch->template_id=$request->template;
        $messageBatch->batch_date_sent=new Carbon($request->send_date);
        if(isset($request->final_date)){
            $messageBatch->final_date =new Carbon($request->final_date);
        }
        $messageBatch->save();


        return $messageBatch;
    }

}
