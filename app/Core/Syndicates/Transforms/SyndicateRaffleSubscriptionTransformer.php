<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicateRaffleSubscription;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="RaffleSyndicateSubscription",
 *     @SWG\Property(
 *       property="identifier",
 *       description="Raffle Syndicate Subscription identifier",
 *       type="integer",
 *      example="1234"
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
 *     @SWG\Property(
 *       property="syndicate_raffle_identifier",
 *       description="Raffle Syndicate Subscription identifier",
 *       type="integer",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="raffle_draw",
 *       type="object",
 *       description="Raffle Draw",
 *       @SWG\Property(property="identifier", type="integer", description="Draw Id", example="1887"),
 *       @SWG\Property(property="name", type="string", description="Raffle Draw Name", example="#GROUP_SORTEO_GORDITO#"),
 *       @SWG\Property(property="type_tag", type="string", description="Raffle Draw Name"),
 *     ),
 *     @SWG\Property(
 *       property="purchase_date",
 *       description="Purchase date",
 *       type="string",
 *       format="date_time",
 *       example="2013-07-26 06:50:06"
 *     ),
 *     @SWG\Property(
 *       property="prizes",
 *       description="Prizes",
 *       type="number",
 *       format="float",
 *       example="13.3"
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       description="Draws",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="emitted",
 *           description="Emitted Draws",
 *           type="integer",
 *           example="1",
 *         ),
 *         @SWG\Property(
 *           property="total",
 *           description="Total Draws",
 *           type="integer",
 *           example="2",
 *         ),
 *       )
 *     ),
 *     @SWG\Property(
 *       property="participations",
 *       description="Participations",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/SyndicateRaffleParticipation"),
 *     ),
 *  )
 */

class SyndicateRaffleSubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateRaffleSubscription $syndicate_raffle_subscription) {
        return [
            'identifier' => (integer)$syndicate_raffle_subscription->rsyndicate_sub_id,
            'status' => $syndicate_raffle_subscription->status,
            'status_tag' => $syndicate_raffle_subscription->status_tag,
            'syndicate_raffle_identifier' => $syndicate_raffle_subscription->rsyndicate_id,
            'raffle_draw' => $syndicate_raffle_subscription->raffle,
            'purchase_date'=> $syndicate_raffle_subscription->sub_buydate,
            'prizes' => $syndicate_raffle_subscription->prizes,
            'draws' => $syndicate_raffle_subscription->draws,
            'participations' => $syndicate_raffle_subscription->participations,
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
