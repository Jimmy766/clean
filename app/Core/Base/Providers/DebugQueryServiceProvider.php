<?php

namespace App\Core\Base\Providers;

use App\Core\Base\Services\GetAllValuesFromHeaderService;
use App\Core\Base\Services\SendLogConsoleService;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Class DebugQueryServiceProvider
 *
 * @package App\Providers
 */
class DebugQueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
            DB::listen(static function ($query) {

                $queryBinding = '';

                $sql = $query->sql;

                $bindings = array_map(static function ($value) {
                    if ($value instanceof DateTime) {
                        return $value->format('Y-m-d H:i:s');
                    }
                    return $value;
                }, $query->bindings);

                foreach ($bindings as $binding) {
                    $queryBinding .= $binding . ', ';
                    $value        = is_numeric($binding) ? $binding : "'$binding'";
                    $sql          = preg_replace('/\?/', $value, $sql, 1);
                }

                $searchWords = ['oauth_access_tokens', 'oauth_clients'];
                if(Str::contains($sql,$searchWords)){
                   return null;
                }

                $request = request();
                $uri = $request->getRequestUri();
                $headers = GetAllValuesFromHeaderService::execute($request);
                $headers = $headers->toArray();
                $origin = array_key_exists('origin', $headers) ? $headers['origin'] : null;
                $idUser     = Auth::id();
                $idUser     = "info-{$idUser}-mysql";
                $channel    = Log::channel('database');
                $arrayQuery = [
                    'query complete:' => $sql,
                    'time'            => $query->time,
                    'from'            => $origin,
                    'uri'             => $uri,
                    'method'          => $request->method(),
                    'id_user'         => $idUser,
                ];
                $arrayQuery = ['data_log' => $arrayQuery];
                $string     = json_encode($arrayQuery);
                $string     = str_replace("\\", '', $string);
                $channel->debug($string, []);
            });
    }
}
