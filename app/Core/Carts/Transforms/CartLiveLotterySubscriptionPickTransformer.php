<?php

namespace App\Core\Carts\Transforms;

use App\Core\Carts\Models\CartLiveLotterySubscriptionPick;
use League\Fractal\TransformerAbstract;

class CartLiveLotterySubscriptionPickTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CartLiveLotterySubscriptionPick $cart_live_lottery_subscription_pick) {
        return [
            'identifier' => (integer)$cart_live_lottery_subscription_pick->ctpck_id,
            'picks' => $cart_live_lottery_subscription_pick->picks
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
            'wheel_balls' => 'cts_wheel_picked_balls',
            'wheel_extra_balls' => 'cts_wheel_picked_extras',
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
            'cts_wheel_picked_balls' => 'wheel_balls',
            'cts_wheel_picked_extras' => 'wheel_extra_balls',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
