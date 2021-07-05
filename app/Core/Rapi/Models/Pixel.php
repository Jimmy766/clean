<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class Pixel extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'pixel_id';
    public $timestamps = false;
    //public $transformer = PriceTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];

    public function PixelTag($user, $cart = null) {
        $pixel_code = $this->pixel_code;
        $pixel_code = str_replace('#USER_ID#', $user->usr_id, $pixel_code);
        $pixel_code = str_replace('#USER_EMAIL#', $user->usr_email, $pixel_code);
        $pixel_code = str_replace('#USER_TRACK#', $user->usr_track, $pixel_code);
        $pixel_code = str_replace('#USER_IP#', request('user_ip'), $pixel_code);
        $pixel_code = str_replace('#USER_REG_DATE#', $user->usr_regdate, $pixel_code);
        $pixel_code = str_replace('#USER_DATA4#', $user->usr_cookies_data4, $pixel_code);
        $pixel_code = str_replace('#USER_DATA5#', $user->usr_cookies_data5, $pixel_code);
        $pixel_code = str_replace('#USER_DATA6#', $user->usr_cookies_data6, $pixel_code);
        if (isset($cart)) {
            $pixel_code = str_replace('#ORDER_ID#', $cart->crt_id, $pixel_code);
            $pixel_code = str_replace('#ORDER_COST#', $cart->crt_price, $pixel_code);
            $pixel_code = str_replace('#ORDER_CURRENCY#', $cart->crt_currency, $pixel_code);
        }
        return $pixel_code;
    }
}
