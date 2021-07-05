<?php

namespace App\Core\Casino\Services;

use App\Core\Cloud\Services\GetCloudUrlService;
use App\Core\Cloud\Services\SetOriginCloudUrlService;
use Illuminate\Support\Str;

/**
 * Class GenerateUrlDemoCasinoGameService
 * @package App\Services
 */
class GenerateUrlDemoCasinoGameService
{

    /**
     * @param int $id
     * @param int $live
     * @return string
     */
    public function execute(int $id, int $live = 0)
    {
        if ($live === 1) {
            return "";
        }

        $token    = request()->header('authorization');
        $token    = explode(" ", $token);
        $token    = $token[ 1 ];
        $token    = base64_encode($token);
        $ip       = request()->user_ip;
        $cloudUrl = \App\Core\Cloud\Services\GetCloudUrlService::execute();
        $url = "{$cloudUrl}/games/?id={$id}&game_mode=demo&user_ip={$ip}&t={$token}";
        return SetOriginCloudUrlService::execute($url);
    }
}
