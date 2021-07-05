<?php


namespace App\Core\Clients\Services;

use App\Core\Base\Services\GetIsoFromOriginTokenSsrService;
use Illuminate\Support\Facades\Cache;

class IP2LocTrillonario

{
    private static $uri = "http://api.ip2loc.svc.cluster.local";
    private static $default_iso = "BR";
    private static $default_region = "MB";


    public static function get_iso($country_from_ip)
    {

        $tokenRequest = request()->header( 'set-tkssr' );
        //check if ip is in cache
        if (Cache::has(request()->user_ip) && $tokenRequest === null) {
            //return from cache
            return Cache::get(request()->user_ip);
        }

        $uriEnv = env('URL_SERVICE_IP2LOCATION', null);
        $uri = $uriEnv === null ? self::$uri : $uriEnv;
        $url = $uri. '/api.php?c_ip=' . request()->user_ip;

        $iso = null;
        $region = null;
        $country = null;
        $appEnv = env('APP_ENV', null) == 'dev' || env('APP_ENV', null) == 'local';

        if ($tokenRequest !== null) {
            [ $iso, $country ] = GetIsoFromOriginTokenSsrService::execute();
            $region = self::$default_region;
            if ($iso !== null) {
                return [ $iso, $region, $country ];
            }
        }

        if ($appEnv && $uriEnv === null){
            $iso = self::$default_iso;
            $region = self::$default_region;
            return [$iso, $region, $country];
        }

        if (empty($country_from_ip)) {

            $header = array("AUTH: fdsA349d8jfdsd5DES");
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            $response = curl_exec($ch);
            $Error = curl_error($ch);


            if (!curl_errno($ch)) {
                curl_close($ch);
            } else {
                $country = 'request fail';
                return [$iso, $region, $country];
            }

            if ($Error == null || $Error == "") {
                $country = json_decode($response, TRUE);
                $iso = $country['code'];
                $region = $country['region'];
            }
        }
        if(!empty($country_from_ip)){
            $iso = $country_from_ip;
            $region = null;
        }

        if (!$iso || $iso == '' || $iso == '-') {
            $iso = null;
            $region = null;
        }

        //move to .env
        $expiresIn = config('constants.cache_daily');
        if (isset($expiresIn)) {
            $data = [$iso, $region, $country];
            Cache::put(request()->user_ip, $data, $expiresIn);
        }

        return [$iso, $region, $country];
    }
}
