<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\LiveLotteryTicket;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LiveLotteryTicket",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID ticket identifier",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="balls",
 *       description="Picks common balls",
 *       type="array",
 *       @SWG\Items(type="integer"),
 *       example="[1,2,3]"
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       description="Picks extra balls",
 *       type="array",
 *       @SWG\Items(type="integer"),
 *       example="[2]"
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       type="number",
 *       format="float",
 *       description="Prize won by the user",
 *       example="1234.5"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="modifier",
 *       description="Lottery Modifier",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/LotteryModifier"),
 *       }
 *     ),
 *  ),
 */

class LiveLotteryTicketTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LiveLotteryTicket $live_lottery_ticket)
    {
        return [
            'identifier' => (integer)$live_lottery_ticket->tck_id,
            'balls' => $live_lottery_ticket->balls,
            'extra_balls' => $live_lottery_ticket->extra_balls,
            'prize' => $live_lottery_ticket->tck_prize_usr,
            'currency' => $live_lottery_ticket->curr_code,
            'modifier' => $live_lottery_ticket->modifier,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }
}
