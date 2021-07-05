<?php

namespace App\Core\Base\Services;

class TranslateTextService
{

    public static function execute($text = '')
    {
        $termTranslated = TranslateInterTermService::execute($text);
        if ($termTranslated !== null) {
            return $termTranslated;
        }
        return __($text);

    }
}
