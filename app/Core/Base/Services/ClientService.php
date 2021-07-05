<?php


namespace App\Core\Base\Services;


use App\Core\Base\Traits\SecretHelper;
use App\Core\Clients\Models\Client;
use Illuminate\Support\Facades\Cache;

class ClientService
{

    /**
     * Devuelve el sistema de un cliente y lo agrega a la cache
     * @param $oauth_client_id
     * @return mixed
     */
    public static function getSystem($oauth_client_id){
        $sys_id = Cache::remember('sys_id_client_id'.$oauth_client_id,
            60, function () use ($oauth_client_id) {
                return Client::where('id', $oauth_client_id)->first()->site->system->sys_id;
            });

        return $sys_id;
    }

    /**
     * Función para que orca se pueda conectar y usar varios sitemas.
     * Básicamente se indica por request que se quiere usar x sistema
     * Si el sistema no es el mismo que el del cliente con el cual está haciendo el request
     * se busca si ese cliente tiene un cliente asociado con ese sistema
     * si es así, usa ese cliente para el request, de lo contrario usa simplemente el que hizo el request
     * @param $client
     * @return Client|null
     */
    public static function getClient($client){
        $sys_id = request()->has("sys_id") ? request()->get("sys_id") : null;
        $client = is_object($client) ? $client : Client::with("site.system")->find($client);

        /**
         * Si no viene sys_id o el sys_id es igual al que tiene el cliente,
         * entonces retorno el mismo cliente, no hay cambios
         */
        if(!$sys_id || $client->site->system->sys_id == $sys_id){
            return $client;
        }

        /**
         * Busco los clientes asociados con ese system
         */
        //$database1 = Config::get('database.connections.mysql_external.database');
        /**
         * esto es un tema de laravel que no hace joins con diferentes db. Hay que decir exactamente de que db es cada uno
         */
        $db_data = SecretHelper::getSecret("mysql_external");
        $database1 = $db_data["database"];

        $assoc_client = Client::join($database1.".sites", "sites.site_id", "=", "oauth_clients.site_id")
            ->where("parent_oauth_client_id", "=", $client->id)
            ->where("sites.sys_id", "=", $sys_id)
            ->first();

        /**
         * Sobreescribo el oauth_client_id en el request
         * porque se usa muchas veces para consultar internamente
         */
        if($assoc_client){
            request()->merge([
               "oauth_client_id" => $assoc_client->id,
                "client_sys_id" => $assoc_client->site->system->sys_id
            ]);
            return $assoc_client;
        }

        return $client;
    }

    /**
     * Nos fijamos por el nombre del cliente, todos los clientes de orca serán llamados "orca"
     * @return bool
     */
    public static function isOrca(){
        $cli = Client::findOrFail(request("oauth_client_id"));
        if(!$cli || $cli->name != "orca"){
            return false;
        }
        return true;
    }
}
