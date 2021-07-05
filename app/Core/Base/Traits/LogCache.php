<?php

namespace App\Core\Base\Traits;

use App\Core\Rapi\Models\LogConfig;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

trait LogCache
{

    public function rememberCache($tag, $period, $func)
    {
        $tc = round(microtime(true) * 1000, 2);
        $result = Cache::remember($tag,  $period, $func);
        return $result;
    }


    public function record_log($type, $tag, $array = [])
    {
        $minutes = config('constants.cache_hourly');
        $log = LogConfig::query()
            ->where('type', '=', $type)
            ->where('active', '=', 1)
            ->where(function (
                $query
            ) {
                $query->whereNull('user_ip')->orWhere('user_ip', '=', request('user_ip'));
            })
            ->getFromCache(['*'],null,$minutes);
        if ($log->isNotEmpty()) {
            $log->each(function ($item) use ($tag, $array) {
                //Remove old log
                $channel = $item->channel;
                if(env('APP_ENV', null) === 'dev' || env('APP_ENV', null) === 'local') {
                    $channel = env('LOG_ELASTIC_CHANNEL') === 'stderr' ? 'stderr' : $item->channel;
                }
                Log::channel($channel)->info('{' . config('app.env') . '} [' . strtoupper
                    ($item->type) .
                    ']: ' . request('tk') . ' --- ' . $tag, $array);
            });
        }
    }

    public function recordLog($type, $tag, $array = [])
    {
        $minutes = config('constants.cache_hourly');
        $log = LogConfig::query()
            ->where('type', '=', $type)
            ->where('active', '=', 1)
            ->where(function (
                $query
            ) {
                $query->whereNull('user_ip')->orWhere('user_ip', '=', request('user_ip'));
            })
            ->getFromCache(['*'],null,$minutes);
        if ($log->isNotEmpty()) {
            $log->each(function ($item) use ($tag, $array) {
                //Remove old log
                $channel = $item->channel;
                if(env('APP_ENV', null) === 'dev' || env('APP_ENV', null) === 'local') {
                    $channel = env('LOG_ELASTIC_CHANNEL') === 'stderr' ? 'stderr' : $item->channel;
                }
                $string = "";
                try {
                    $string = json_encode($array);
                }
                catch(Exception $exception) {
                    $string = "error encode log";
                }
                $string = str_replace("\\", '', $string);
                Log::channel($channel)->info($string);
            });
        }
    }

    public static function record_log_static($type, $tag, $array = [])
    {
        $minutes = config('constants.cache_hourly');
        $log = LogConfig::query()
            ->where('type', '=', $type)
            ->where('active', '=', 1)
            ->where(function ($query) {
                $query->whereNull('user_ip')->orWhere('user_ip', '=', request('user_ip'));
            })
            ->getFromCache(['*'],null,$minutes);
        if ($log->isNotEmpty()) {
            $log->each(function ($item) use ($tag, $array) {
                //Remove old log
                Log::channel($item->channel)->info('[' . strtoupper($item->type) . ']: ' . request('tk') . ' --- ' . $tag, $array);
            });
        }
    }
}
