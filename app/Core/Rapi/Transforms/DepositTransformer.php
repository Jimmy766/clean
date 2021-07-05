<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Deposit;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Deposit",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Deposit identifier",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date-time",
 *       description="Deposit date",
 *       example="2010-03-27 16:40:22",
 *     ),
 *     @SWG\Property(
 *       property="payment_method",
 *       type="number",
 *       format="float",
 *       description="Deposit payment method",
 *       example="13.45",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Deposit currency",
 *       example="USD",
 *     ),
 *     @SWG\Property(
 *       property="amount",
 *       type="number",
 *       format="float",
 *       description="Deposit amount",
 *       example="13.45",
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Deposit price",
 *       example="13.45",
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="Payment status",
 *       example="ORDERS_DETAIL_CONFIRMED",
 *     ),
 *  ),
 */

class DepositTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Deposit $deposit) {
        return [
            'identifier' => (integer)$deposit->crt_id,
            'date' => $deposit->crt_buyDate,
            'payment_method' => $deposit->payment_method,
            'currency' => $deposit->crt_currency,
            'amount' => $deposit->crt_total,
            'price' => $deposit->crt_price,
            'status' => $deposit->status,
        ];
    }

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
