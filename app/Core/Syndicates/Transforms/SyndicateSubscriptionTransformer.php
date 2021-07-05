<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicateSubscription;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicateSubscription",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Syndicate Subscription identifier",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="Syndicate Subscription Status",
 *       example="#SUBSCRIPTION_DETAIL_STATUS_EXPIRED#",
 *     ),
 *     @SWG\Property(
 *       property="syndicate_identifier",
 *       type="integer",
 *       description="Syndicate id",
 *       example="123",
 *     ),
 *     @SWG\Property(
 *       property="lottery",
 *       type="object",
 *       @SWG\Property(property="identifier", type="integer", example="23"),
 *       @SWG\Property(property="name", type="string", example="Saturday Lotto"),
 *       @SWG\Property(property="region", type="string", example="Australia"),
 *     ),
 *     @SWG\Property(
 *       property="purchase_date",
 *       type="string",
 *       format="date",
 *       description="Purchase Date",
 *       example="2018-03-06 08:30:52",
 *     ),
 *     @SWG\Property(
 *       property="prizes",
 *       description="Prizes",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(property="currency", type="string", description="Currency", example="USD"),
 *         @SWG\Property(property="prize", type="number", format="float", description="Price", example="0.11"),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       description="Draws",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(property="emitted", type="integer", description="Emited", example="1"),
 *         @SWG\Property(property="total", type="integer", description="Total", example="3"),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="participations",
 *       description="Participations",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/SyndicateParticipation"),
 *     ),
 *  ),
 */

class SyndicateSubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateSubscription $syndicate_subscription) {
        return [
            'identifier' => (integer)$syndicate_subscription->syndicate_sub_id,
            'status' => $syndicate_subscription->status,
            'syndicate_identifier' => $syndicate_subscription->syndicate_id,
            'lottery' => $syndicate_subscription->lottery,
            'purchase_date'=>$syndicate_subscription->sub_buydate,
            'prizes' => $syndicate_subscription->prizes,
            'draws' => $syndicate_subscription->sub_draws,
            'participations' => $syndicate_subscription->participations,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'syndicate_sub_id',
            'status' => 'status',
            'syndicate_identifier' => 'syndicate_id',
            'lottery' => 'lottery',
            'prizes' => 'prizes',
            'draws' => 'sub_draws',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'syndicate_sub_id' => 'identifier',
            'status' => 'status',
            'syndicate_id' => 'syndicate_identifier',
            'lottery' => 'lottery',
            'prizes' => 'prizes',
            'sub_draws' => 'draws',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
