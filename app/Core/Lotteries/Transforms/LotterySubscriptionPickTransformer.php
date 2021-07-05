<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\LotterySubscriptionPick;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotterySubscriptionPick",
 *     @SWG\Property(
 *       property="pick_balls",
 *       type="array",
 *       description="Pick balls",
 *       @SWG\Items(ref="#/definitions/Ball"),
 *       example="[1,2,3]",
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       type="array",
 *       description="Extra balls",
 *       @SWG\Items(ref="#/definitions/Ball"),
 *       example="[1,2,3]",
 *     ),
 *  ),
 */


class LotterySubscriptionPickTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LotterySubscriptionPick $lottery_subscription_pick) {
        return [
            'pick_balls' => $lottery_subscription_pick->lot_balls,
            'extra_balls' => $lottery_subscription_pick->extra_balls,
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
