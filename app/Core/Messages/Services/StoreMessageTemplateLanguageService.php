<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\MessageTemplateLanguage;

/**
 * Class StoreMessageTemplateService
 * @package App\Services
 */
class StoreMessageTemplateLanguageService
{


    public function execute($request)
    {
        $messageTemplateLanguage=new MessageTemplateLanguage();

        $messageTemplateLanguage->template_id=$request->template_id;
        $messageTemplateLanguage->site_id=$request->site_id;
        $messageTemplateLanguage->language=$request->language;
        $messageTemplateLanguage->body=$request->body;
        $messageTemplateLanguage->subject=$request->subject;

        $messageTemplateLanguage->save();


        return $messageTemplateLanguage;
    }

}
