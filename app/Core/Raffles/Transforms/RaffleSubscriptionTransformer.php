<?php

namespace App\Core\Raffles\Transforms;

use App\Core\Raffles\Models\RaffleSubscription;
use League\Fractal\TransformerAbstract;


/**
 * @SWG\Definition(
 *     definition="RaffleSubscription",
 *     @SWG\Property(
 *       property="identifier",
 *       description="Raffle Subscription identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="draw_identifier",
 *       description="Raffle draw identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="draw_extra_identifier",
 *       description="Raffle draw extra identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       description="Name of Raffle",
 *       type="string",
 *       example="#SPAIN_THURSDAY#"
 *     ),
 *     @SWG\Property(
 *       property="type_tag",
 *       type="string",
 *       description="Raffle type tag",
 *       example="#LOTERIA_NACIONAL_RAFFLE_TYPE1#"
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       description="Prize",
 *       type="number",
 *       format="float",
 *       example="13.3"
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       description="Currency",
 *       type="string",
 *       example="USD"
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       description="Buy date",
 *       type="string",
 *       example="active"
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       description="Draws",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="total",
 *           description="Total Draws",
 *           type="integer",
 *           example="2",
 *         ),
 *         @SWG\Property(
 *           property="emitted",
 *           description="Emitted Draws",
 *           type="integer",
 *           example="1",
 *         ),
 *       )
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       description="Subscription status",
 *       type="string",
 *       example="active"
 *     ),
 *     @SWG\Property(
 *       property="status_tag",
 *       description="Subscription status tag",
 *       type="string",
 *       example="#SUBSCRIPTION_DETAIL_STATUS_ACTIVE#"
 *     ),
 *  )
 */





class RaffleSubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(RaffleSubscription $raffle_subscription) {
        return [
            'identifier' => $raffle_subscription->crf_id,
            'raffle_id' => $raffle_subscription->inf_id,
            'draw_identifier' => $raffle_subscription->draw_id,
            'draw_extra_identifier' => $raffle_subscription->draw_extra_id,
            'name' => $raffle_subscription->raffle_name,
            'type_tag' => $raffle_subscription->raffle_type_tag,
            'prize' => $raffle_subscription->prizes,
            'currency' => $raffle_subscription->currency,
            'date' => $raffle_subscription->rsub_buydate,
            'draws' => $raffle_subscription->draws,
            'status' => $raffle_subscription->status,
            'status_tag' => $raffle_subscription->status_tag,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'status' => 'status',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'status' => 'status',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
