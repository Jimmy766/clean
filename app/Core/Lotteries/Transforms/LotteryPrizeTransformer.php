<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\LotteryPrize;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryPrize",
 *     required={"pick_balls","extra_balls","refund_balls"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Draw identifier",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="pick_balls",
 *       type="integer",
 *       description="Pick balls",
 *       example="3",
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
 *       type="number",
 *       format="float",
 *       description="Prize value",
 *       example="3",
 *     ),
 *  ),
 */

class LotteryPrizeTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LotteryPrize $lottery_prize) {
        return [
            'identifier' => (integer)$lottery_prize->lop_id,
            'pick_balls' => (integer)$lottery_prize->lop_balls,
            'extra_balls' => (integer)$lottery_prize->lop_extras,
            'refund' => (integer)$lottery_prize->lop_reintegro,
            //'prize' => $lottery_prize->lop_prize,
            'prize' => round((float)$lottery_prize->lop_prize,2),
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'lop_id',
            'lottery_identifier' => 'lot_id',
            'pick_balls' => 'lop_balls',
            'extra_balls' => 'lop_extras',
            'refund' => 'lop_reintegro',
            'prize' => 'lop_prize',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'lop_id' => 'identifier',
            'lot_id' => 'lottery_identifier',
            'lop_balls' => 'pick_balls',
            'lop_extras' => 'extra_balls',
            'lop_reintegro' => 'refund',
            'lop_prize' => 'prize',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
