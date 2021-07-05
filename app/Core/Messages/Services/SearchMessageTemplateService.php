<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\MessageTemplate;

/**
 * Class SearchMessageTemplateService
 * @package App\Services
 */
class SearchMessageTemplateService
{

    public function execute($request)
    {
        $site=$request->site_id;
        $system=$request->system_id;
        $name=$request->name;
        $language=$request->language;
        $subject=$request->subject;

        $relations=['messageTemplateLanguage','messageTemplateCategory','messageTemplateType'];
        $messageTemplateQuery=MessageTemplate::with($relations);
        $messageTemplateQuery=$this->querySystem($messageTemplateQuery,$system);
        $messageTemplateQuery=$this->querySite($messageTemplateQuery,$site);
        $messageTemplateQuery=$this->queryLanguage($messageTemplateQuery,$language);
        $messageTemplateQuery=$this->querySubject($messageTemplateQuery,$subject);
        $messageTemplateQuery=$this->queryName($messageTemplateQuery,$name);

        return $messageTemplateQuery->paginateByRequest();

    }

    public function querySystem($messageTemplateQuery, $system_id)
    {
        if(isset($system_id)) {
            $messageTemplateQuery=$messageTemplateQuery->where('sys_id', $system_id);
        }
        return $messageTemplateQuery;
    }
    private function queryName($messageTemplateQuery, $name)
    {
        if( isset($name)){
            $messageTemplateQuery=$messageTemplateQuery->where('template_name','like','%'.$name.'%');
        }
        return $messageTemplateQuery;
    }
    private function querySite($messageTemplateQuery, $site_id)
    {
        if(isset($site_id)){
            $messageTemplateQuery=$messageTemplateQuery->whereHas('messageTemplateLanguage',function ($query) use ($site_id){
                $query->where('site_id',$site_id);
            });
        }
        return $messageTemplateQuery;
    }
    private function queryLanguage($messageTemplateQuery, $language)
    {
        if(isset($language)){
            $messageTemplateQuery=$messageTemplateQuery->whereHas('messageTemplateLanguage',function ($query) use ($language){
                $query->where('language','like','%'.$language.'%');
            });
        }
        return $messageTemplateQuery;
    }
    private function querySubject($messageTemplateQuery, $subject)
    {
        if(isset($subject)){
            $messageTemplateQuery=$messageTemplateQuery->whereHas('messageTemplateLanguage',function ($query) use ($subject){
                $query->where('subject','like','%'.$subject.'%');
            });
        }
        return $messageTemplateQuery;
    }

}
