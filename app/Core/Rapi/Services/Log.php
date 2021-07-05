<?php


namespace App\Core\Rapi\Services;


use App\Core\Rapi\Models\LogConfig;
use Illuminate\Support\Facades\Cache;

class Log
{

    public static function record_log($type, $tag, $array = []) {
        $log = LogConfig::query()
            ->where('type', '=', $type)
            ->where('active', '=', 1)
            ->where(
                function ($query) {
                    $query->whereNull('user_ip')
                        ->orWhere('user_ip', '=', request('user_ip'));
                }
            )
            ->getFromCache();

        if ($log->isNotEmpty()) {
            $log->each(function ($item) use ($tag, $array) {
                \Illuminate\Support\Facades\Log::channel($item->channel)
                    ->info('[' . strtoupper($item->type) . ']: '. request('tk') . ' --- ' . $tag, $array);
            });
        }
    }

    public static function stringify($item){
        return str_replace(array("\n", "\r"), '', var_export($item, true));
    }

    public static function debug($log){
        file_put_contents(storage_path("logs/debug.log"), self::stringify($log), FILE_APPEND | LOCK_EX);
    }
}
