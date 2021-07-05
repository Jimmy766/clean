<?php


namespace App\Core\Carts\Services;


use Illuminate\Support\Facades\Cache;

class CartUtil
{

    /**
     * @param $crt_id
     * @return bool
     */
    public static function unlock_cart($crt_id) {
        if (self::is_locked($crt_id)) {
            Cache::forget('lock_cart_' . $crt_id);
            return true;
        }
        return false;
    }

    /**
     * @param $crt_id
     * @return bool
     */
    public static function is_locked($crt_id){
        return Cache::has('lock_cart_' . $crt_id);
    }

}
