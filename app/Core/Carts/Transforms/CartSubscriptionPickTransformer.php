<?php

namespace App\Core\Carts\Transforms;

use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="CartSubscriptionPick",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Identifier ID",
 *       example="123",
 *     ),
 *     @SWG\Property(
 *       property="pick_balls",
 *       type="array",
 *       description="Balls picks",
 *       @SWG\Items(type="integer"),
 *       example="[1,2,3]",
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       type="array",
 *       description="Extra balls picks",
 *       @SWG\Items(type="integer"),
 *       example="[1,2,3]",
 *     ),
 *     @SWG\Property(
 *       property="wheel_balls",
 *       description="Wwheel balls",
 *       type="integer",
 *       example="6"
 *     ),
 *     @SWG\Property(
 *       property="wheel_extra_balls",
 *       description="Wheel extra balls",
 *       type="integer",
 *       example="2"
 *     ),
 *  ),
 */

class CartSubscriptionPickTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart_subscription_pick) {
        return [
            'identifier' => (integer)$cart_subscription_pick->ctpck_id,
            'cart_subscription_identifier' => $cart_subscription_pick->cts_id,
            'pick_balls' => $cart_subscription_pick->lot_balls,
            'extra_balls' => $cart_subscription_pick->extra_balls,
            'wheel_balls' => $cart_subscription_pick->cts_wheel_picked_balls_arr,
            'wheel_extra_balls'=> $cart_subscription_pick->cts_wheel_picked_extras_arr,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'ctpck_id',
            'cart_subscription_identifier' => 'cts_id',
            'number1' => 'ctpck_1',
            'number2' => 'ctpck_2',
            'number3' => 'ctpck_3',
            'number4' => 'ctpck_4',
            'number5' => 'ctpck_5',
            'number6' => 'ctpck_6',
            'number7' => 'ctpck_7',
            'number8' => 'ctpck_8',
            'number9' => 'ctpck_9',
            'number10' => 'ctpck_10',
            'number11' => 'ctpck_11',
            'number12' => 'ctpck_12',
            'wheel_balls' => 'cts_wheel_picked_balls_arr',
            'wheel_extra_balls' => 'cts_wheel_picked_extras_arr',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'ctpck_id' => 'identifier',
            'cts_id' => 'cart_subscription_identifier',
            'ctpck_1' => 'number1',
            'ctpck_2' => 'number2',
            'ctpck_3' => 'number3',
            'ctpck_4' => 'number4',
            'ctpck_5' => 'number5',
            'ctpck_6' => 'number6',
            'ctpck_7' => 'number7',
            'ctpck_8' => 'number8',
            'ctpck_9' => 'number9',
            'ctpck_10' => 'number10',
            'ctpck_11' => 'number11',
            'ctpck_12' => 'number12',
            'cts_wheel_picked_balls_arr' => 'wheel_balls',
            'cts_wheel_picked_extras_arr' => 'wheel_extra_balls',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
