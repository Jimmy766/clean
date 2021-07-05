<?php

namespace App\Core\Carts\Transforms;

use App\Core\AdminLang\Services\AL;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicateRaffleSubscription",
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
 *       property="raffle_syndicate_identifier",
 *       description="Raffle Syndicate identifier",
 *       type="integer",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="raffle_syndicate_name",
 *       description="Raffle Syndicate name",
 *       type="string",
 *       example="#SYNDICATE_RAFFLE_NAME_ELPADRE#"
 *     ),
 *     @SWG\Property(
 *       property="purchase_date",
 *       description="Purchase date",
 *       type="string",
 *       format="date_time",
 *       example="2013-07-26 06:50:06"
 *     ),
 *     @SWG\Property(
 *       property="subscriptions",
 *       description="Subscriptions",
 *       type="integer",
 *       example="2"
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
 *       property="prizes",
 *       description="Prizes",
 *       type="integer",
 *       example="20"
 *       ),
 *     ),
 *   @SWG\Property(
 *       property="draw_date",
 *       description="Draw date",
 *       type="string",
 *       format="date_time",
 *       example="2013-07-26 06:50:06"
 *       ),
 *     ),
 *  )
 */

class CartRaffleSyndicateTransformer2 extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart_raffle_syndicate) {
        $syndicate_raffle = $cart_raffle_syndicate->syndicate_raffle ? $cart_raffle_syndicate->syndicate_raffle : null;
        return [
            'identifier' => $cart_raffle_syndicate->cts_id,
            'order' => $cart_raffle_syndicate->crt_id,
            'status' => $cart_raffle_syndicate->status,
            'status_tag' => $cart_raffle_syndicate->status_tag,
            'raffle_syndicate_identifier' => $cart_raffle_syndicate->rsyndicate_id,
            'currency' => $syndicate_raffle->currency,
            'raffle_syndicate_name' => $syndicate_raffle ? AL::translate(str_replace("#", "", $syndicate_raffle->syndicate_raffle_name)) : null,
            'purchase_date' => $cart_raffle_syndicate->purchase_date,
            'subscriptions' => $cart_raffle_syndicate->subscriptions,
            'draws' => $cart_raffle_syndicate->draws,
            'prizes' => $cart_raffle_syndicate->prizes,
            'draw_date' => $cart_raffle_syndicate->syndicate_raffle->date,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cts_id',
            'order' => 'crt_id',
            'status' => 'status',
            'raffle_syndicate_identifier' => 'syndicate_id',
            'raffle_syndicate_name' => 'syndicate_name',
            'purchase_date' => 'purchase_date',
            'subscriptions' => 'subscriptions',
            'draws' => 'draws',
            'prizes' => 'prizes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cts_id' => 'identifier',
            'crt_id' => 'order',
            'status' => 'status',
            'syndicate_id' => 'syndicate_identifier',
            'syndicate_name' => 'syndicate_name',
            'purchase_date' => 'purchase_date',
            'subscriptions' => 'subscriptions',
            'draws' => 'draws',
            'prizes' => 'prizes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
