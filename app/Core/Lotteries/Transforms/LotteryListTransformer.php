<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Rapi\Resources\RoutingFriendlyResource;
use App\Core\Lotteries\Models\Lottery;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryList",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Lottery identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of lottery",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="draw_date",
 *       description="Next draw date",
 *       type="string",
 *       format="date-time",
 *       example="2018-01-01 12:00:00",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="jackpot",
 *       type="number",
 *       description="Jackpot",
 *       example="2000000"
 *     ),
 *     @SWG\Property(
 *       property="jackpot_in_usd",
 *       type="number",
 *       description="Jackpot in USD",
 *       example="2000000"
 *     ),
 *     @SWG\Property(
 *       property="region",
 *       description="Region of lottery",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Region"),
 *       }
 *     ),
 *  ),
 */

class LotteryListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(Lottery $lottery) {
        $result = [
            'identifier' => (integer)$lottery->lot_id,
            'name' => (string)$lottery->name,
            'draw_date' => $lottery->draw_date,
            'currency' => (string)$lottery->currency,
            'jackpot' => $lottery->jackpot,
            'jackpot_in_usd' => $lottery->jackpot_usd,
            'big_lotto' => $lottery->big_lotto,
            'region' => $lottery->region_attributes,
            'insure_boosted_jackpot' => $lottery->insure_boosted_jackpot,
            'boosted_jackpot' =>  $lottery->boosted_jackpot_attributes,
            'routing_friendly' => $lottery->routing_friendly_attributes,
        ];
        if ($lottery->lot_id == 25) {
            $result['raffle_jackpot'] = '#LOTTERY_GENERAL_EURO_UK_INFO_RAFFLE#';
        }
        return $result;
    }

    public static function originalAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
