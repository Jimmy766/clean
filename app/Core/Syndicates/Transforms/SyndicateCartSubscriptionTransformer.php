<?php

namespace App\Core\Syndicates\Transforms;

use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CartSyndicateSubscription",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="456"
 *     ),
 *     @SWG\Property(
 *       property="cart_id",
 *       type="integer",
 *       format="int32",
 *       description="Cart ID",
 *       example="123"
 *     ),
 *     @SWG\Property(
 *       property="syndicate",
 *       description="Syndicate attributes",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/SyndicateCart"), }
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Syndicate price",
 *       example="0.1"
 *     ),
 *     @SWG\Property(
 *       property="extra_ticket",
 *       type="integer",
 *       description="Extra ticket",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="tickets_by_draw",
 *       description="Tickets by draw",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="next_draw_ticket",
 *       description="Next draw ticket",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="renewable",
 *       description="Renewable subscription",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="price_attributes",
 *       description="Price attributes",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/SyndicatePrice"), }
 *     ),
 *     @SWG\Property(
 *       property="bonus_products",
 *       description="Bonus product list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/BonusProductDetail"),
 *     ),
 *     @SWG\Property(
 *       property="cart_timestamp",
 *       description="Cart adding timestamp",
 *       type="date_time",
 *       example="2018-11-13 15:13:17"
 *     ),
 *   ),
 */

class SyndicateCartSubscriptionTransformer extends TransformerAbstract
{

    /**
     * @param $syndicate_cart_subscription
     * @return array
     */

    public static function transform($syndicate_cart_subscription) {
        return [
            'identifier' => (integer)$syndicate_cart_subscription->cts_id,
            'cart_id' => (integer)$syndicate_cart_subscription->crt_id,
            'syndicate' => $syndicate_cart_subscription->syndicate_attributes,
            'price' => round((float)$syndicate_cart_subscription->cts_price,2),
            'extra_ticket' => $syndicate_cart_subscription->cts_ticket_extra,
            'tickets_by_draw' => $syndicate_cart_subscription->participations,
            'next_draw_ticket' => $syndicate_cart_subscription->cts_ticket_nextDraw,
            'renewable' => $syndicate_cart_subscription->cts_renew,
            'price_attributes' => $syndicate_cart_subscription->price_attributes,
            'bonus_products' => $syndicate_cart_subscription->bonus_products,
            'cart_timestamp' => $syndicate_cart_subscription->cart_time_stamp,
            'syndicate_picks_id' => $syndicate_cart_subscription->wheel_picks
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cts_id',
            'cart_id' => 'crt_id',
            'syndicate_id' => 'syndicate_id',
            'price' => 'cts_price',
            'extra_ticket' => 'cts_ticket_extra',
            'tickets_by_draw' => 'cts_ticket_byDraw',
            'next_draw_ticket' => 'cts_ticket_nextDraw',
            'renewable' => 'cts_renew',
            'bonus_id' => 'bonus_id',
            'price_id' => 'cts_syndicate_prc_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cts_id' => 'identifier',
            'crt_id' => 'cart_id',
            'syndicate_id' => 'syndicate_id',
            'cts_price' => 'price',
            'cts_extra_ticket' => 'ticket_extra',
            'cts_ticket_byDraw' => 'tickets_by_draw',
            'cts_ticket_nextDraw' => 'next_draw_ticket',
            'cts_renew' => 'renewable',
            'bonus_id' => 'bonus_id',
            'cts_syndicate_prc_id' => 'price_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
