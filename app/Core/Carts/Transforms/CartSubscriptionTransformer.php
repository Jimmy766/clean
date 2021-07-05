<?php

namespace App\Core\Carts\Transforms;

use App\Core\Lotteries\Models\Lottery;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CartSubscription",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="Cart Subscription identifier",
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
 *       example="Saturday Lotto"
 *     ),
 *     @SWG\Property(
 *       property="lottery_region",
 *       description="Lottery region",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/Region"), }
 *     ),
 *     @SWG\Property(
 *       property="draw_date",
 *       type="string",
 *       description="Next draw date",
 *       example="2015-10-31 02:30:00"
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       type="number",
 *       format="float",
 *       description="Count of Tickets",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Lottery price",
 *       example="0.1"
 *     ),
 *     @SWG\Property(
 *       property="extra_ticket",
 *       type="integer",
 *       description="Extra ticket",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="pick_type",
 *       description="Pick type",
 *       type="integer",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="tickets_by_draw",
 *       description="Tickets by draw",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       description="Draws Quantity",
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
 *       property="winning_behaviour",
 *       description="Winning behaviour",
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
 *       property="ticket_name",
 *       description="Ticket name",
 *       type="string",
 *       example="Ticket 1"
 *     ),
 *     @SWG\Property(
 *       property="draws_by_ticket",
 *       description="Draws by ticket",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="day_to_play",
 *       description="Day to play",
 *       type="integer",
 *       example="7"
 *     ),
 *     @SWG\Property(
 *       property="bonus",
 *       description="Bonus ID",
 *       type="integer",
 *       example="765"
 *     ),
 *     @SWG\Property(
 *       property="cart_timestamp",
 *       description="Cart adding timestamp",
 *       type="date_time",
 *       example="2018-11-13 15:13:17"
 *     ),
 *     @SWG\Property(
 *       property="wheel",
 *       description="Wheel",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/Wheel"), }
 *     ),
 *     @SWG\Property(
 *       property="price_attributes",
 *       description="Price attributes",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/LotteryPrice"), }
 *     ),
 *     @SWG\Property(
 *       property="cart_subscription_picks_attributes",
 *       description="Draw for subscription",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/CartSubscriptionPick"),
 *     ),
 *   ),
 */

class CartSubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart_subscription) {
        $crt_sub =  [
            'identifier' => (integer)$cart_subscription->cts_id,
            'cart_id' => (integer)$cart_subscription->crt_id,
            'lottery_id' => (integer)$cart_subscription->lot_id,
            'lottery_name' => $cart_subscription->lottery_name,
            'lottery_region' => $cart_subscription->lottery_region,
            'lottery_boosted_jackpot' => $cart_subscription->lottery_boosted_jackpot,
            'insure_boosted_jackpot' => $cart_subscription->insure_lottery_jackpot,
            'draw_date' => $cart_subscription->draw_date,
            'tickets' => $cart_subscription->cts_tickets,
            'price' => round((float)$cart_subscription->cts_price,2),
            'extra_ticket' => $cart_subscription->cts_ticket_extra,
            'pick_type'=>$cart_subscription->cts_pck_type,
            'tickets_by_draw' => $cart_subscription->cts_ticket_byDraw,
            'next_draw_ticket' => $cart_subscription->cts_ticket_nextDraw,
            'winning_behaviour'  => $cart_subscription->cts_winning_behaviour,
            'renewable' => $cart_subscription->cts_renew,
            'ticket_name' => $cart_subscription->cts_printable_name,
            'draws_by_ticket' => $cart_subscription->cts_draws_by_ticket,
            'day_to_play' => $cart_subscription->cts_day_to_play,
            'bonus' => $cart_subscription->bonus_id,
            'cart_timestamp' => $cart_subscription->cart_time_stamp,
            'wheel' => $cart_subscription->wheel_attributes,
            'price_attributes' => $cart_subscription->price_attributes,
            'cart_subscription_picks_attributes' => $cart_subscription->cart_subscription_picks_attributes,
            'paused' => $cart_subscription->paused,
            'price_modifier_1' => $cart_subscription->cts_modifier_1,
            'price_modifier_2' => $cart_subscription->cts_modifier_2,
            'price_modifier_3' => $cart_subscription->cts_modifier_3,
            'boosted_modifier' => $cart_subscription->boosted_modifier_id,
        ];

        if($cart_subscription->lot_id == Lottery::$CASH4LIFE_ID){
            $crt_sub += [
                "first_day_to_play" => $cart_subscription->first_day_to_play->first_datetoplay
            ];
        }

        return $crt_sub;
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
            'draws' => 'draws',
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
            'price_id' => 'prc_id',
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
            'draws' => 'draws',
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
            'prc_id' => 'price_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
