<?php

    namespace App\Core\Lotteries\Transforms;

    use App\Core\Lotteries\Models\LiveLotterySubscription;
    use League\Fractal\TransformerAbstract;

    /**
     * @SWG\Definition(
     *     definition="LiveLotterySubscription",
     *     @SWG\Property(
     *       property="identifier",
     *       description="Live Lottery Subscription identifier",
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
     *       property="lottery_id",
     *       description="Live Lottery identifier",
     *       type="integer",
     *      example="1234"
     *     ),
     *     @SWG\Property(
     *       property="lottery_name",
     *       description="Name of Live Lottery",
     *       type="string",
     *       example="Quick5"
     *     ),
     *     @SWG\Property(
     *       property="purchase_date",
     *       description="Subscription Purchase Date ",
     *       type="string",
     *       format="date-time",
     *       example="2017-06-09 10:40:02"
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
     *       allOf={
     *         @SWG\Schema(ref="#/definitions/LiveLotterySubscriptionPick"),
     *       }
     *     ),
     *     @SWG\Property(
     *       property="modifier",
     *       description="Modifier",
     *       type="object",
     *       allOf={ @SWG\Schema(ref="#/definitions/LotteryModifier"), }
     *     ),
     *     @SWG\Property(
     *       property="extra_details",
     *       description="Draw and ticket for subscription",
     *       type="object",
     *        @SWG\Property(
     *         property="draw",
     *         description="Draw for subscription",
     *         type="object",
     *         allOf={ @SWG\Schema(ref="#/definitions/LiveDraw"), }
     *        ),
     *        @SWG\Property(
     *         property="ticket",
     *         description="Ticket for subscription",
     *         type="object",
     *         allOf={  @SWG\Schema(ref="#/definitions/LiveLotteryTicket"), }
     *        ),
     *     ),
     *  ),
     */

    class LiveLotterySubscriptionTransformer extends TransformerAbstract {
        /**
         * A Fractal transformer.
         *
         * @return array
         */
        public static function transform(LiveLotterySubscription $live_lottery_subscription) {
            return [
                'identifier' => (integer)$live_lottery_subscription->sub_id,
                'order' => $live_lottery_subscription->cart_subscription ? (integer)$live_lottery_subscription->cart_subscription->crt_id : null,
                'lottery_identifier' => $live_lottery_subscription->lot_id,
                'lottery_name' => $live_lottery_subscription->lottery ? $live_lottery_subscription->lottery->lot_name : '',
                'purchase_date' => $live_lottery_subscription->sub_buydate,
                'status' => $live_lottery_subscription->status,
                'status_tag' => $live_lottery_subscription->status_tag,
                'bet' => $live_lottery_subscription->cart_subscription ? $live_lottery_subscription->cart_subscription->cts_price : null,
                'pick_type' => $live_lottery_subscription->pck_type,
                'pick_type_text' => $live_lottery_subscription->pick_type_text,
                'picks' => $live_lottery_subscription->picks,
                'modifier' => $live_lottery_subscription->modifier_attributes,
                'extra_details' => [
                    'draw' => $live_lottery_subscription->draw_attributes,
                    'ticket' => $live_lottery_subscription->ticket_attributes,
                ]
            ];
        }

        public static function originalAttribute($index) {
            $attributes = [
                'identifier' => 'sub_id',
                'lottery' => 'lot_id',
                'buy_date' => 'sub_buydate',
                'status' => 'status',
                'pick_type' => 'pck_type',
                'modifier' => 'modifier_1',
            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }

        public static function transformedAttribute($index) {
            $attributes = [
                'sub_id' => 'identifier',
                'lot_id' => 'lottery',
                'sub_tickets' => 'tickets',
                'sub_buydate' => 'buy_date',
                'status' => 'status',
                'pck_type' => 'pick_type',
                'modifier_1' => 'modifier',
            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }
    }
