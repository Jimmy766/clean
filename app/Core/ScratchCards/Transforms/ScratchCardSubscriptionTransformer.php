<?php

namespace App\Core\ScratchCards\Transforms;

use App\Core\ScratchCards\Models\ScratchCardSubscription;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="ScratchCardSubscription",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="order",
 *       type="integer",
 *       format="int32",
 *       description="Order ID",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="scratch_id",
 *       type="integer",
 *       format="int32",
 *       description="Scratch card ID",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="scratch_name",
 *       type="integer",
 *       format="int32",
 *       description="Scratch card name",
 *       example="305"
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
 *       property="prize",
 *       type="number",
 *       format="float",
 *       description="Prize",
 *       example="10.1"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="rounds",
 *       type="integer",
 *       description="Rounds",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="rounds_free",
 *       type="integer",
 *       description="Rounds free",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="rounds_extra",
 *       type="integer",
 *       description="Rounds extra",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="remaining_rounds",
 *       type="integer",
 *       description="Remaining rounds",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="remaining_rounds_free",
 *       type="integer",
 *       description="Remaining rounds free",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="real_url",
 *       type="string",
 *       description="Scratch card url to play real game",
 *       example="https://www.example.com/scratch_cards?id=1&game_mode=real_play"
 *     ),
 *   ),
 */

class ScratchCardSubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(ScratchCardSubscription $scratch_subscription) {
        $partOneRemainingRound = round ($scratch_subscription->sub_rounds+$scratch_subscription->sub_rounds_free,0);
        $partTwoRemainingRound = round($scratch_subscription->sub_emitted+$scratch_subscription ->sub_emitted_free,0);
        return [
            'identifier' => (integer)$scratch_subscription->scratches_sub_id,
            'order' => $scratch_subscription->cart_subscription ? (integer)$scratch_subscription->cart_subscription->crt_id : null,
            'scratch_id' => $scratch_subscription->scratches_id,
            'scratch_name' => $scratch_subscription->scratch_name,
            'purchase_date' => $scratch_subscription->sub_buydate,
            'status' => $scratch_subscription->status,
            'status_tag' => $scratch_subscription->status_tag,
            'prize' => round($scratch_subscription->prize,2),
            'currency' => auth()->user()->curr_code,
            'rounds' => $scratch_subscription->sub_rounds + $scratch_subscription->sub_rounds_free,
            'remaining_rounds' => $partOneRemainingRound - $partTwoRemainingRound,
            'real_url' => $scratch_subscription->real_play_url,
        ];
    }

    /**
     * @param $index
     *
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'scratches_sub_id',
            'order' => 'crt_id',
            'scratch' => 'scratches_id',
            'status' => 'status',
            'status_tag' => 'status_tag',
            'prize' => 'prize',
            'rounds' => 'sub_rounds',
            'rounds_free' => 'sub_rounds_free',
            'rounds_extra' => 'sub_rounds_extra',
            'remaining_rounds' => 'remaining_rounds',
            'remaining_rounds_extra' => 'remaining_rounds_extra',
        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'scratches_sub_id' => 'identifier',
            'crt_id' => 'order',
            'scratches_id' => 'scratch',
            'status' => 'status',
            'status_tag' => 'status_tag',
            'prize' => 'prize',
            'sub_rounds' => 'rounds',
            'sub_rounds_free' => 'rounds_free',
            'sub_rounds_extra' => 'rounds_extra',
            'remaining_rounds' => 'remaining_rounds',
            'remaining_rounds_extra' => 'remaining_rounds_extra',
        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }
}
