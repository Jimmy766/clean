<?php

namespace App\Core\Base\Services;

use Illuminate\Support\Facades\Cache;

class GetCharsetGeneralServices
{
    public static function execute()
    {
        $app = app();
        try {
            $infoReturn = [];
            $connections = $app['config']['database.connections'];
            $infoReturn = self::getCharsetFromConnectionInfo($connections, $infoReturn);
            if(count($infoReturn)<=0){
                $cacheKeyMysql                       = "database-config-mysql";
                $cacheKeyMysqlExternal               = "database-config-mysql";
                $connections[ 'mysql' ]        = Cache::get($cacheKeyMysql);
                $connections[ 'mysql_external' ] = Cache::get($cacheKeyMysqlExternal);
                $infoReturn = self::getCharsetFromConnectionInfo($connections, $infoReturn);
            }
            $infoReturn['charset_php'] = ini_get('default_charset');
            return json_encode($infoReturn);
        }
        catch(Exception $exception) {
            return '';
        }

    }

    private static function getCharsetFromConnectionInfo($connections, $infoReturn)
    {

        if(array_key_exists('mysql', $connections)){
            if(array_key_exists('charset', $connections['mysql'])){
                $infoReturn['charset_database_rapi'] = $connections['mysql']['charset'];
            }
        }
        if(array_key_exists('mysql_external', $connections)){
            if(array_key_exists('charset', $connections['mysql_external'])){
                $infoReturn['charset_database_trillonario'] = $connections['mysql_external']['charset'];
            }
        }

        return $infoReturn;
    }

}
