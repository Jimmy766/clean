<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\LotteryLevel;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryLevel",
 *     required={"pick_balls","extra_balls","refund_balls"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID lottery level identifier",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="pick_balls",
 *       type="integer",
 *       description="Pick balls",
 *       example="123",
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       type="integer",
 *       description="Extra balls",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="refund",
 *       type="integer",
 *       description="Refund value",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       type="integer",
 *       description="Prize level",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="odds",
 *       type="integer",
 *       description="Odds",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="prize_type",
 *       type="integer",
 *       description="Prize type",
 *       example="1",
 *     ),
 *  ),
 */

class LotteryLevelTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LotteryLevel $lottery_level) {
        return [
            'identifier' => (integer)$lottery_level->lol_id,
            'pick_balls' => (integer)$lottery_level->lol_balls,
            'extra_balls' => (integer)$lottery_level->lol_extras,
            'refund' => (integer)$lottery_level->lol_reintegro,
            //'prize' => $lottery_level->lol_prize,
            'prize' => round((float)$lottery_level->lol_prize,2),
            'odds' => $lottery_level->lol_odds,
            'prize_type' => (integer)$lottery_level->lol_prize_type,

        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'lol_id',
            'lottery_identifier' => 'lot_id',
            'pick_balls' => 'lol_balls',
            'extra_balls' => 'lol_extras',
            'refund' => 'lol_reintegro',
            'prize' => 'lol_prize',
            'odds' => 'lol_odds',
            'prize_type' => 'lol_prize_type',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'lol_id' => 'identifier',
            'lot_id' => 'lottery_identifier',
            'lol_balls' => 'pick_balls',
            'lol_extras' => 'extra_balls',
            'lol_reintegro' => 'refund',
            'lol_prize' => 'prize',
            'lol_odds' => 'odds',
            'lol_prize_type' => 'prize_type',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
