<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\PaymentTransformer;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    public $transformer = PaymentTransformer::class;
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

    public function payway() {
        return $this->hasOne(Payway::class, 'pay_id', 'pay_id');
    }


    public function payout_requested() {
        return $this->hasOne(PayoutMethod::class, 'payout_id', 'payout_requested_id');
    }

    public function getPayMethodAttribute() {
        $payway = $this->payway ? $this->payway : null;
        $payout_payway = $payway ? $payway->payout : null;
        if ($payout_payway) {
            return '#PAYOUT_NAME_'.$payout_payway->name.'#';
        } else {
            $payout_requested = $this->payout_requested;
            if ($payout_requested) {
                return '#PAYOUT_NAME_'.$payout_requested->name.'#';
            } else {
                return '#NAME_OTHER#';
            }
        }
    }

    public function getPaymentStatusAttribute() {
        return $this->status == 'paid' ? '#PAYMENTS_DETAIL_STATUS_CONFIRMED#' : '#PAYMENTS_DETAIL_STATUS_PENDING#';
    }
}
