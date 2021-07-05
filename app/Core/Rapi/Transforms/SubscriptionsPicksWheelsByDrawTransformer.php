<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\SubscriptionsPicksWheelsByDraw;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="SubscriptionPicksWheelsByDraw",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="SubscriptionPicksWheelsByDraw Id",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="picked_balls",
 *       type="array",
 *       description="Picked balls",
 *       @SWG\Items(ref="#/definitions/Ball"),
 *       example="[1,2,3]",
 *     ),
 *     @SWG\Property(
 *       property="extra_picked_balls",
 *       type="array",
 *       description="Extra Picked balls",
 *       @SWG\Items(ref="#/definitions/Ball"),
 *       example="[1,2,3]",
 *     ),
 *   ),
 */

class SubscriptionsPicksWheelsByDrawTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SubscriptionsPicksWheelsByDraw $subscriptions_picks_wheels_by_draw) {
        return [
            'identifier' => $subscriptions_picks_wheels_by_draw->spwb_id,
            'picked_balls' => $subscriptions_picks_wheels_by_draw->balls,
            'extra_picked_balls' => $subscriptions_picks_wheels_by_draw->extra_balls,
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
