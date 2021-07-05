<?php


namespace App\Core\AdminLang\Services;


use App\Core\AdminLang\Services\AdminLang;

class AL
{
    public static function translate($tag, $lang=""){
        return AdminLang::getInstance()->translate($tag, $lang);
    }

    public static function translate_page($page, $sys_id, $lang){
        return AdminLang::getInstance()->getSection($page, $sys_id, $lang);
    }

    public static function getPage($page){
        $available_pages = [
            "terms" => "terms_insurance_2c"
        ];

        return isset($available_pages[$page]) ? $available_pages[$page] : false;
    }
}
