<?php

namespace App\Core\Base\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

/**
 * Class LocationService
 * @package App\Core\Base\Services
 */
class LocationService
{

    /**
     * @param $request
     * @param $app
     */
    public function setLanguageByHeaders($request, $app = null)
    {
        // read the language from the request header
        $locale = $request->header('Content-Language');

        if ($app !== null) {
            // if the header is missed
            if ( !$locale) {
                // take the default local language
                $locale = $app->config->get('app.locale');
            }

            // check the languages defined is supported
            if ( !array_key_exists($locale, $app->config->get('app.supported_languages'))) {
                // respond with error
                return abort(Response::HTTP_FORBIDDEN, 'Language not supported.');
            }

            // set the local language
            $app->setLocale($locale);
        } else {
            App::setLocale($locale);
        }

        return $locale;

    }
}
