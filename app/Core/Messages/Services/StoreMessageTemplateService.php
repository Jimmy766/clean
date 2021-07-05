<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\MessageTemplate;

/**
 * Class StoreMessageTemplateService
 * @package App\Services
 */
class StoreMessageTemplateService
{


    public function execute($request)
    {
        $messageTemplate=new MessageTemplate();

        $messageTemplate->sys_id=$request->system;
        $messageTemplate->template_name=$request->name;
        $messageTemplate->template_type=$request->type;
        $messageTemplate->template_category=$request->category;

        $messageTemplate->save();


        return $messageTemplate;
    }

}
