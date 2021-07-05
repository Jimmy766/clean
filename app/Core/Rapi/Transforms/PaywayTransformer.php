<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Payway;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Payway",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Payway identifier",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Payway name",
 *       example="BankTransfer",
 *     ),
 *     @SWG\Property(
 *       property="type",
 *       type="integer",
 *       description="Payway type 0: Alternative method. 1: Debit/Credit cards",
 *       example="0",
 *     ),
 *  )
 */

class PaywayTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Payway $payway) {
        return [
            'identifier' => $payway->pay_id,
            'name' => $payway->pay_show_name,
            'type' => $payway->pay_type,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'pay_id',
            'name' => 'pay_name',
            'type' => 'pay_type',
            'cart_id' => 'crt_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'pay_id' => 'identifier',
            'pay_name' => 'name',
            'pay_type' => 'type',
            'crt_id' => 'cart_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
