<?php

namespace App\Core\Cloud\Services;

use App\Core\Base\Services\GetOriginRequestService;
use Illuminate\Support\Str;

class SetOriginCloudUrlService
{

    public static function execute($cloudUrl)
    {
        $searches        = [ 'wintrillions.com' ];
        $origin          = GetOriginRequestService::execute();
        $domainException = env('DOMAIN_STATIC_EXCEPTION', null);
        if (Str::contains($origin, $searches) === true && empty($domainException) === false) {
            $cloudUrl .= '&origin=' . $domainException;
        }
        return $cloudUrl;
    }
}
