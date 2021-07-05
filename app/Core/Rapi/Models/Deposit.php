<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\DepositTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Deposit extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'crt_id';
    protected $table = 'carts';
    const CREATED_AT = 'crt_date';
    const UPDATED_AT = 'crt_lastupdate';
    public $transformer = DepositTransformer::class;

    public function payway() {
        return $this->hasOne(Payway::class, 'pay_id', 'pay_id');
    }

    public function billing() {
        $user_id = Auth::user()->usr_id;
        return $this->hasOne(Billing::class, 'crt_id', 'crt_id')
            ->where('bil_success', '=', 1)->where('usr_id', '=', $user_id);
    }

    public function getPaymentMethodAttribute() {
        if ($this->pay_id == 41) {
            return '#PAY_METHOD_BONUS#';
        } elseif ($this->pay_id == 246){
            return '#PAY_METHOD_PRESALE#';
        } elseif ($this->crt_pay_method == 0) {
            return '#PAY_METHOD_ACCOUNT#';
        } elseif ($this->crt_pay_method == 2) {
            return '#PAY_METHOD_MIX# ' + $this->payway->pay_name;
        } else {
            $billNumShow = __("empty-billing-number");
            $billing = $this->billing;
            if (isset($billing))
                if (isset($billing->bil_ccNum_show)) {
                $billNumShow = $billing->bil_ccNum_show;
            }
            return $this->payway->pay_name . ' ' . $billNumShow;
        }
    }

    public function getStatusAttribute() {
        if ($this->crt_status == 3 || $this->crt_status == 7) {
            return '#ORDERS_DETAIL_CANCELLED#';
        } else {
            return '#ORDERS_DETAIL_CONFIRMED#';
        }
    }
}
