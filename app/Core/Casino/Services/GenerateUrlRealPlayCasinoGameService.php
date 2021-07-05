<?php

namespace App\Core\Casino\Services;

use App\Core\Cloud\Services\GetCloudUrlService;
use App\Core\Cloud\Services\SetOriginCloudUrlService;
use Illuminate\Support\Str;

/**
 * Class GenerateUrlRealPlayCasinoGameService
 * @package App\Services
 */
class GenerateUrlRealPlayCasinoGameService
{

    /**
     * @param int $id
     * @return string
     */
    public function execute(int $id)
    {
        $token    = request()->header('authorization');
        $token    = explode(" ", $token);
        $token    = $token[ 1 ];
        $token    = base64_encode($token);
        $ip       = request()->user_ip;
        $cloudUrl = \App\Core\Cloud\Services\GetCloudUrlService::execute();
        $url = "{$cloudUrl}/games/?id={$id}&game_mode=real_play&user_ip={$ip}&t={$token}";
        $url = \App\Core\Cloud\Services\SetOriginCloudUrlService::execute($url);
        return request()->user_id ? $url : '';
    }
}
