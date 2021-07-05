<?php

namespace App\Core\Base\Services;

class GetOriginRequestService
{

    public static function execute()
    {
        $request = request();
        $headers = GetAllValuesFromHeaderService::execute($request);
        $headers = $headers->toArray();

        return array_key_exists('origin', $headers) ? $headers['origin'] : null;

    }
}
