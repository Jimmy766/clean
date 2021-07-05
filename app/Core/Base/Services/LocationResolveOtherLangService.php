<?php

namespace App\Core\Base\Services;

use App\Core\Base\Classes\ModelConst;

/**
 * Class LocationResolveOtherLangService
 * @package App\Core\Base\Services
 */
class LocationResolveOtherLangService
{

    /**
     * @return string
     */
    public static function execute(): string
    {

        $locale = app()->getLocale();
        $lang = ModelConst::TRILLONARIO_LANGUAGE_ENGLISH;
        $lang = $locale === ModelConst::LOCALE_LANGUAGE_SPANISH ? ModelConst::TRILLONARIO_LANGUAGE_SPANISH : $lang;
        $lang = $locale === ModelConst::LOCALE_LANGUAGE_PORTUGUES ? ModelConst::TRILLONARIO_LANGUAGE_PORTUGUESE : $lang;

        return $lang;
    }

}
