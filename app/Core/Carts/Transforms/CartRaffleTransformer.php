<?php

namespace App\Core\Carts\Transforms;

use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CartRaffle",
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
 *       property="raffle_id",
 *       type="integer",
 *       format="int32",
 *       description="Raffle ID",
 *       example="789"
 *     ),
 *     @SWG\Property(
 *       property="raffle_draw_id",
 *       type="integer",
 *       format="int32",
 *       description="Raffle Next Draw ID",
 *       example="987"
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       type="number",
 *       format="float",
 *       description="Quantity of Tickets",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="tickets_by_draw",
 *       description="Tickets by draw",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="next_draw_tickets",
 *       description="Next draw ticket",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Raffle price",
 *       example="45"
 *     ),
 *     @SWG\Property(
 *       property="play_method",
 *       type="integer",
 *       description="Play Method",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="price_attributes",
 *       description="Price attributes",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/RafflePrice"), }
 *     ),
 *     @SWG\Property(
 *       property="cart_timestamp",
 *       description="Cart adding timestamp",
 *       type="date_time",
 *       example="2018-11-13 15:13:17"
 *     ),
 *   ),
 */

class CartRaffleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart_raffle) {
        return [
            'identifier' => (integer)$cart_raffle->crf_id,
            'cart_id' => (integer)$cart_raffle->crt_id,
            'raffle_id' => (integer)$cart_raffle->inf_id,
            'raffle_draw_id' => (integer)$cart_raffle->rff_id,
            'tickets' => $cart_raffle->crf_tickets,
            'tickets_by_draw' => $cart_raffle->crf_ticket_byDraw,
            'next_draw_tickets' => $cart_raffle->crf_ticket_nextDraw,
            'price' => round((float)$cart_raffle->crf_price,2),
            'play_method' => $cart_raffle->crf_play_method,
            'price_attributes' => $cart_raffle->price_attributes,
            'raffle' => $cart_raffle->raffle_attributes,
            'cart_timestamp' => $cart_raffle->cart_time_stamp,
            'renew' => $cart_raffle->crf_renew
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'crf_id',
            'cart_id' => 'crt_id',
            'raffle_id' => 'inf_id',
            'raffle_draw_id' => 'rff_id',
            'price_id' => 'crf_prc_rff_id',
            'tickets' => 'crf_tickets',
            'tickets_by_draw' => 'crf_ticket_byDraw',
            'next_draw_ticket' => 'crf_ticket_nextDraw',
            'price' => 'crf_price',
            'play_method' => 'crf_play_method',
            'renew' => 'crf_renew'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'crf_id' => 'identifier',
            'crt_id' => 'cart_id',
            'inf_id' => 'raffle_id',
            'rff_id' => 'raffle_draw_id',
            'crf_prc_rff_id' => 'price_id',
            'crf_tickets' => 'tickets',
            'crf_ticket_byDraw' => 'tickets_by_draw',
            'crf_ticket_nextDraw' => 'next_draw_ticket',
            'crf_price' => 'price',
            'crf_play_method' => 'play_method',
            'crf_renew' => 'renew'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
