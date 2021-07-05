<?php

namespace App\Core\Base\Services;

use App\Core\Base\Classes\DirtyQuery;
use Illuminate\Support\Facades\DB;

class FastTrackLogService extends FastTrackSendLogService
{

    public static function loginUser()
    {
        $post                      = [];
        $post[ 'ip_address' ]      = request('user_ip');
        $post[ 'is_impersonated' ] = false;
        $post[ 'timestamp' ]       = date('c');
        $post[ 'user_agent' ]      = request('user_agent') ? request('user_agent') : '';
        $post[ 'user_id' ]         = "" . request('user_id');
        $post[ 'origin' ]          = request('client_domain');
        return self::post('login', json_encode($post));
    }

    public static function registerUser($user_id)
    {
        $sql      = DirtyQuery::getQueryUserRegisterLog($user_id);
        $userData = DB::connection('mysql_external')
            ->select($sql);
        $userData = $userData[ 0 ];

        $post[ 'timestamp' ] = date('c');

        $bool_keys = [
            "allows_sms_marketing",
            "allows_post_marketing",
            "allows_notification_marketing",
            "allows_email_marketing",
            "allows_call_marketing",
        ];
        $utf8_keys = [ "first_name", "last_name", "city", "address" ];

        foreach ($userData as $key => $value) {
            if ($key == 'user_id') {
                $post[ $key ] = "" . $value;
            } elseif ( !is_numeric($key) && !in_array($key, $bool_keys) && $key != "0" && !in_array(
                    $key, $utf8_keys
                )) {
                $post[ $key ] = $value;
            } elseif (in_array($key, $bool_keys)) {
                $post[ $key ] = ( $value ? true : false );
            } elseif (in_array($key, $utf8_keys)) {
                $post[ $key ] = utf8_encode($value);
            }
        }
        return self::post('user', json_encode($post));
    }

}
