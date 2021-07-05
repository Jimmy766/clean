<?php

namespace App\Core\Cloud\Services;

use App\Core\Base\Services\GetOriginRequestService;
use Illuminate\Support\Str;

class GetCloudUrlService
{

    public static function execute()
    {
        $cloudUrl =  env('CLOUD_BASE_URL','https://cloud.trillonario.com');
        $searches = ['wintrillions.com'];
        $origin = GetOriginRequestService::execute();
        $env = env('APP_ENV', null);
        if (Str::contains($origin, $searches) === true && $env === 'prod') {
            $cloudUrl = 'https://cloud.wintrillions.com';
        }
        return $cloudUrl;
    }
}
