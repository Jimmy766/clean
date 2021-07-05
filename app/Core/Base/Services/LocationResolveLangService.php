<?php

namespace App\Core\Base\Services;

use App\Core\Base\Classes\ModelConst;

/**
 * Class LocationResolveLangService
 * @package App\Core\Base\Services
 */
class LocationResolveLangService
{

    public static function execute($parameterLang)
    {
        $lang = ModelConst::LOCALE_LANGUAGE_ENGLISH;
        $lang = $parameterLang === ModelConst::TRILLONARIO_LANGUAGE_SPANISH ? ModelConst::LOCALE_LANGUAGE_SPANISH :
            $lang;
        $lang = $parameterLang === ModelConst::TRILLONARIO_LANGUAGE_PORTUGUESE ? ModelConst::LOCALE_LANGUAGE_PORTUGUES : $lang;

        return $lang;
    }

}
