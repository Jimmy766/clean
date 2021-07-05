<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Payment;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Payment",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Payment identifier",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date-time",
 *       description="Payment date",
 *       example="2010-03-27 16:40:22",
 *     ),
 *     @SWG\Property(
 *       property="amount",
 *       type="number",
 *       format="float",
 *       description="Payment amount",
 *       example="13.45",
 *     ),
 *     @SWG\Property(
 *       property="amount_currency",
 *       type="string",
 *       description="Payment amount currency",
 *       example="USD",
 *     ),
 *     @SWG\Property(
 *       property="user_amount",
 *       type="number",
 *       format="float",
 *       description="Payment user amount",
 *       example="13.45",
 *     ),
 *     @SWG\Property(
 *       property="user_amount_currency",
 *       type="string",
 *       description="Payment user amount currency",
 *       example="EUR",
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="Payment status",
 *       example="PAYMENTS_DETAIL_STATUS_CONFIRMED",
 *     ),
 *  )
 */

class PaymentTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Payment $payment) {
        return [
            'identifier' => $payment->id,
            'date' => $payment->date_request,
            'pay_method' => $payment->pay_method,
            'amount' => $payment->amount,
            'amount_currency' => $payment->amount_currency,
            'user_amount' => $payment->amount_usr,
            'user_amount_currency' => $payment->amount_usr_curr,
            'status' => $payment->payment_status,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
