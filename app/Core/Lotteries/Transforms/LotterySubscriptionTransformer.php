<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\LotterySubscription;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotterySubscription",
 *     @SWG\Property(
 *       property="identifier",
 *       description="Lottery Subscription identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="order",
 *       description="Order identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="lottery_identifier",
 *       description="Lottery identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="lottery_name",
 *       description="Name of Lottery",
 *       type="string",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="purchase_date",
 *       description="Subscription Purchase Date ",
 *       type="string",
 *       format="date-time",
 *       example="2017-06-09 10:40:02"
 *     ),
 *     @SWG\Property(
 *       property="subscriptions",
 *       description="Subscriptions quantity",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="extra_ticket",
 *       description="Extra Tickets quantity",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       description="Emitted draws / Total draws",
 *       type="string",
 *       example="1 / 1"
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       description="Prize",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="prize_value",
 *           description="Prize Value",
 *           type="string",
 *           example="USD : 10"
 *         ),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="prize_pending",
 *       description="Prize Pending",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="prize_pending_value",
 *           description="Prize Pending Value",
 *           type="string",
 *           example="USD : 10"
 *         ),
 *       ),
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
 *       property="pick_type",
 *       description="Pick type",
 *       type="string",
 *       example="#QUICK_PICK#"
 *     ),
 *     @SWG\Property(
 *       property="picks",
 *       description="User Picks",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/LotterySubscriptionPick"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="wheel",
 *       description="Wheel",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Wheel"),
 *       }
 *     ),
 *  ),
 */

class LotterySubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LotterySubscription $subscription) {
        return [
            'identifier' => (integer)$subscription->sub_id,
            'order' => $subscription->order,
            'lottery_identifier' => $subscription->lot_id,
            'lottery_name' => $subscription->lottery_name,
            'lottery_draw_date' => $subscription->lottery_draw_date,
            'lottery_region' => $subscription->lottery_region['name'],
            'lottery_boosted_jackpot' => $subscription->lottery_boosted_jackpot,
            'purchase_date'=> $subscription->sub_buydate,
            'subscriptions' => $subscription->subscriptions,
            'extra_ticket' => $subscription->sub_ticket_extra,
            'draws_emitted' => $subscription->sub_draws['emitted'],
            'draws_total' => $subscription->sub_draws['total'],
            'prize' => $subscription->prize,
            'prize_pending' => $subscription->prize_pending,
            'status' => $subscription->status,
            'status_tag' => $subscription->status_tag,
            'pick_type' => $subscription->pick_type,
            'picks' => $subscription->picks,
            'wheel' => $subscription->wheel,
            'play_mode' => $subscription->play_mode,
            'descriptor' => $subscription->descriptor,
            'model_type' => $subscription->model_type,
            'price_modifier_1'    => $subscription->cart_suscription[ 'price_modifier_1' ],
            'price_modifier_2'    => $subscription->cart_suscription[ 'price_modifier_2' ],
            'price_modifier_3'    => $subscription->cart_suscription[ 'price_modifier_3' ],
            'boosted_modifier' => $subscription->cart_suscription[ 'boosted_modifier' ],
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'sub_id',
            'lottery_id' => 'lot_id',
            'tickets' => 'sub_tickets',
            'extra_ticket' => 'sub_ticket_extra',
            'buy_date'=>'sub_buydate',
            'status'=>'status',
            'tickets_emitted' =>'sub_emitted',
            'last_draw' => 'sub_lastdraw_id',
            'extension' => 'sub_Extension',
            'next_draw_ticket' => 'cts_ticket_nextDraw',
            'tickets_next_draw'  => 'sub_ticket_nextDraw',
            'cart_subscription_id' => 'cts_id',
            'parent' => 'sub_parent',
            'first_renew' => 'sub_root',
            'renewable' => 'sub_renew',
            'pick_type' => 'pck_type',
            'system' => 'sys_id',
            'site' => 'site_id',
            'subtype' => 'sub_subtype',
            'last_draw_email' => 'sub_lastDraw_email',
            'ticket_by_draw' => 'sub_ticket_byDraw',
            'on_hold' => 'on_hold',
            'selector_type' => 'sub_type_selector',
            'selector_cant' => 'sub_cant_selector',
            'winning_behaviour' => 'sub_winning_behaviour',
            'notes' => 'sub_notes',
            'printable_name' => 'sub_printable_name',
            'draws_by_ticket' => 'sub_draws_by_ticket',
            'day_to_play' => 'sub_day_to_play',
            'wheel' => 'wheel_id',
            'next_draw' => 'sub_next_draw_id',
            'multiplier' => 'sub_multiplier',
            'modifier1' => 'modifier_1',
            'modifier2' => 'modifier_2',
            'modifier3' => 'modifier_3',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'sub_id' => 'identifier',
            'lot_id' => 'lottery_id',
            'sub_tickets' => 'tickets',
            'sub_ticket_extra' => 'extra_ticket',
            'sub_buydate'=>'buy_date',
            'status'=>'status',
            'sub_emitted' =>'tickets_emitted',
            'sub_lastdraw_id' => 'last_draw',
            'sub_Extension' => 'extension',
            'cts_ticket_nextDraw' => 'next_draw_ticket',
            'sub_ticket_nextDraw'  => 'tickets_next_draw',
            'cts_id' => 'cart_subscription_id',
            'sub_parent' => 'parent',
            'sub_root' => 'first_renew',
            'sub_renew' => 'renewable',
            'pck_type' => 'pick_type',
            'sys_id' => 'system',
            'site_id' => 'site',
            'sub_subtype' => 'subtype',
            'sub_lastDraw_email' => 'last_draw_email',
            'sub_ticket_byDraw' => 'ticket_by_draw',
            'on_hold' => 'on_hold',
            'sub_type_selector' => 'selector_type',
            'sub_cant_selector' => 'selector_cant',
            'sub_winning_behaviour' => 'winning_behaviour',
            'sub_notes' => 'notes',
            'sub_printable_name' => 'printable_name',
            'sub_draws_by_ticket' => 'draws_by_ticket',
            'sub_day_to_play' => 'day_to_play',
            'wheel_id' => 'wheel',
            'sub_next_draw_id' => 'next_draw',
            'sub_multiplier' => 'multiplier',
            'modifier1' => 'modifier_1',
            'modifier2' => 'modifier_2',
            'modifier3' => 'modifier_3',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
