<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\LotteriesBoostedJackpot;
use App\Core\Lotteries\Models\Lottery;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryBoostedJackpot",
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
 *  ),
 */

class LotteryBoostedJackPotTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(LotteriesBoostedJackpot $boostedJackpot) {
        $result = [
            'identifier' => (integer)$boostedJackpot->id,
            'identifier_modifier' => (integer)$boostedJackpot->modifier_id,
            'identifier_lottery' => (integer)$boostedJackpot->lot_id,
            'value' => $boostedJackpot->boost_value,
            'lotteries_modifier' => $boostedJackpot->lotteries_modifier_attributes
        ];
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
