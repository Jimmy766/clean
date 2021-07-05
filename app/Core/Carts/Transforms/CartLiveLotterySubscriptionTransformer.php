<?php

namespace App\Core\Carts\Transforms;

use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="BetsLiveLottery",
 *     required={"cart","lottery","draws","tickets"},
 *     @SWG\Property(property="cart", type="integer", example="17317936"),
 *     @SWG\Property(property="lottery", type="integer", example="43"),
 *     @SWG\Property(property="draws", type="array", @SWG\Items(type="integer"), example="[56285,56284]"),
 *     @SWG\Property(property="tickets", type="array",
 *       @SWG\Items(
 *         type="object",
 *         @SWG\Property(property="bet", type="number", format="float", example="0.1"),
 *         @SWG\Property(property="picks", type="array", @SWG\Items(type="integer"),example="[3,2,1]"),
 *         @SWG\Property(property="modifier_id", type="integer", example="7"),
 *       )
 *     ),
 *   ),
 *   @SWG\Definition(
 *     definition="CartLiveLotterySubscription",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="crt_id",
 *       type="integer",
 *       format="int32",
 *       description="Cart ID",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="lottery_id",
 *       type="integer",
 *       format="int32",
 *       description="Lottery ID",
 *       example="789"
 *     ),
 *     @SWG\Property(
 *       property="lottery_name",
 *       type="string",
 *       description="Lottery name",
 *       example="Quick 3"
 *     ),
 *     @SWG\Property(
 *       property="draw_date",
 *       type="string",
 *       description="Next draw date",
 *       example="2015-10-31 02:30:00"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Bet price",
 *       example="0.1"
 *     ),
 *     @SWG\Property(
 *       property="pick_type",
 *       description="Pick type",
 *       type="integer",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="pick_type_text",
 *       description="Pick type",
 *       type="string",
 *       example="Numbers chosen by the customer"
 *     ),
 *     @SWG\Property(
 *       property="picks",
 *       description="User Picks",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/LiveLotterySubscriptionPick"), }
 *     ),
 *     @SWG\Property(
 *       property="modifier",
 *       description="Modifier",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/LotteryModifier"), }
 *     ),
 *     @SWG\Property(
 *       property="draw_number",
 *       description="Draw number",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="cart_timestamp",
 *       description="Cart adding timestamp",
 *       type="date_time",
 *       example="2018-11-13 15:13:17"
 *     ),
 *   ),
 */

class CartLiveLotterySubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart_live_lottery_subscription) {
        return [
            'identifier' => (integer)$cart_live_lottery_subscription->cts_id,
            'cart_id' => (integer)$cart_live_lottery_subscription->crt_id,
            'lottery_id' => (integer)$cart_live_lottery_subscription->lot_id,
            'lottery_name' => $cart_live_lottery_subscription->lottery_name,
            'draw_date' => $cart_live_lottery_subscription->draw_date,
            'price' => round((float)$cart_live_lottery_subscription->cts_price,2),
            'pick_type'=>$cart_live_lottery_subscription->cts_pck_type,
            'pick_type_text' => $cart_live_lottery_subscription->pick_type_text,
            'picks' => $cart_live_lottery_subscription->picks,
            'modifier' => $cart_live_lottery_subscription->modifier_attributes,
            'draw_number' => $cart_live_lottery_subscription->draw_number,
            'cart_timestamp' => $cart_live_lottery_subscription->cart_time_stamp,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cts_id',
            'cart_id' => 'crt_id',
            'lottery_id' => 'lot_id',
            'tickets' => 'cts_tickets',
            'price' => 'cts_price',
            'extra_ticket' => 'cts_ticket_extra',
            'pick_type' => 'cts_pck_type',
            'tickets_by_draw' => 'cts_ticket_byDraw',
            'draws' => 'cts_draws',
            'next_draw_ticket' => 'cts_ticket_nextDraw',
            'winning_behaviour'  => 'cts_winning_behaviour',
            'renewable' => 'cts_renew',
            'ticket_name' => 'cts_printable_name',
            'draws_by_ticket' => 'cts_draws_by_ticket',
            'day_to_play' => 'cts_day_to_play',
            'wheel' => 'wheel_attributes',
            'bonus' => 'bonus_id',
            'price_attributes' => 'price_attributes',
            'cart_subscription_picks_attributes' => 'cart_subscription_picks_attributes',
            'next_draw_attributes' => 'next_draw_attributes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cts_id' => 'identifier',
            'crt_id' => 'cart_id',
            'lot_id' => 'lottery_id',
            'cts_tickets' => 'tickets',
            'cts_price' => 'price',
            'cts_ticket_extra' => 'extra_ticket',
            'cts_pck_type' => 'pick_type',
            'cts_ticket_byDraw' => 'tickets_by_draw',
            'cts_draws' => 'draws',
            'cts_ticket_nextDraw' => 'next_draw_ticket',
            'cts_winning_behaviour'  => 'winning_behaviour',
            'cts_renew' => 'renewable',
            'cts_printable_name' => 'ticket_name',
            'cts_draws_by_ticket' => 'draws_by_ticket',
            'cts_day_to_play' => 'day_to_play',
            'wheel_attributes' => 'wheel',
            'bonus_id' => 'bonus',
            'price_attributes' => 'price_attributes',
            'cart_subscription_picks_attributes' => 'cart_subscription_picks_attributes',
            'next_draw_attributes' => 'next_draw_attributes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
