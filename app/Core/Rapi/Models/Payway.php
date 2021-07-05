<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\PaywayTransformer;
use Illuminate\Database\Eloquent\Model;

class Payway extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'pay_id';
    public $timestamps = false;
    public $transformer = PaywayTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pay_name',
        'pay_type',
        'country_id',
        'pay_temp',
        'pay_active_site',
        'pay_order_site',
        'pay_active_telemarketer',
        'pay_active_admin',
        'pay_active_tri',
        'pay_active_emm',
        'pay_active_blo',
        'pay_active_cgl',
        'pay_active_mobile',
        'pay_active_payout',
        'black_country_list',
        'black_country_list_emm',
        'black_country_list_blo',
        'black_country_list_cgl',
        'currency',
        'payout_currency_allowed',
        'payout_country_allowed',
        'payout_country_black',
        'payout_id',
        'pay_active_activationflow',
        'merchant_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'pay_id',
        'pay_name',
        'pay_type',
        'country_id',
        'pay_temp',
        'pay_active_site',
        'pay_order_site',
        'pay_active_telemarketer',
        'pay_active_admin',
        'pay_active_tri',
        'pay_active_emm',
        'pay_active_blo',
        'pay_active_cgl',
        'pay_active_mobile',
        'pay_active_payout',
        'black_country_list',
        'black_country_list_emm',
        'black_country_list_blo',
        'black_country_list_cgl',
        'currency',
        'payout_currency_allowed',
        'payout_country_allowed',
        'payout_country_black',
        'payout_id',
        'pay_active_activationflow',
        'merchant_id',
    ];

    public function payout() {
        return $this->belongsTo(PayoutMethod::class, 'payout_id', 'payout_id');
    }

    public function getCountriesAttribute() {
        return explode(',', $this->country_id);
    }

    public function getCountryBlacklistAttribute() {
        return explode(',', $this->black_country_list);
    }

    public function getCurrenciesAttribute() {
        return explode(',', $this->currency);
    }

    public function getTestUsersAttribute() {
        return explode(',', $this->user_test);
    }

}
