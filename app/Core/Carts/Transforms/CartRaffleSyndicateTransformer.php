<?php

namespace App\Core\Carts\Transforms;

use League\Fractal\TransformerAbstract;


/**
 *   @SWG\Definition(
 *     definition="CartRaffleSyndicateSubscription",
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
 *       property="raffle_syndicate",
 *       description="Syndicate attributes",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/RaffleSyndicate"), }
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Raffle Syndicate price",
 *       example="10"
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       type="integer",
 *       description="Tickets quantity",
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
 *       allOf={ @SWG\Schema(ref="#/definitions/SyndicateRafflePrice"), }
 *     ),
 *     @SWG\Property(
 *       property="cart_timestamp",
 *       description="Cart adding timestamp",
 *       type="date_time",
 *       example="2018-11-13 15:13:17"
 *     ),
 *   ),
 */


class CartRaffleSyndicateTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart_raffle_syndicate) {
        return [
            'identifier' => (integer)$cart_raffle_syndicate->cts_id,
            'cart_id' => (integer)$cart_raffle_syndicate->crt_id,
            'raffle_syndicate' => $cart_raffle_syndicate->raffle_syndicate,
            'price' => round((float)$cart_raffle_syndicate->cts_price,2),
            'tickets' => $cart_raffle_syndicate->participations,
            'renewable' => $cart_raffle_syndicate->cts_renew,
            'price_attributes' => $cart_raffle_syndicate->syndicate_raffle_price,
            'cart_timestamp' => $cart_raffle_syndicate->cart_time_stamp,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cts_id',
            'cart_id' => 'crt_id',
            'raffle_syndicate_id' => 'rsyndicate_id',
            'price' => 'cts_price',
            'tickets' => 'cts_ticket_byDraw',
            'renewable' => 'cts_renew',
            'price_id' => 'cts_syndicate_prc_id',
            'bonus' => 'bonus_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cts_id' => 'identifier',
            'crt_id' => 'cart_id',
            'rsyndicate_id' => 'raffle_syndicate_id',
            'cts_price' => 'price',
            'cts_ticket_byDraw' => 'tickets',
            'cts_renew' => 'renewable',
            'cts_syndicate_prc_id' => 'price_id',
            'bonus_id' => 'bonus',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
