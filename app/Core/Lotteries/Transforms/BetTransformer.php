<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Lotteries\Models\Bet;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Bet",
 *     required={"min_bet","max_bet"},
 *     @SWG\Property(
 *       property="min_bet",
 *       type="number",
 *       format="float",
 *       description="Min bet value",
 *       example="0.1"
 *     ),
 *     @SWG\Property(
 *       property="max_bet",
 *       type="number",
 *       format="float",
 *       description="Max bet value",
 *       example="50.0"
 *     ),
 *  ),
 */

class BetTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Bet $bet) {
        return [
            'min_bet' => $bet->min_bet,
            'max_bet' => $bet->max_bet,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'lottery_id' => 'lot_id',
            'min_bet' => 'min_bet',
            'max_bet' => 'max_bet',
            'currency' => 'curr_code',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'lot_id' => 'lottery_id',
            'min_bet' => 'min_bet',
            'max_bet' => 'max_bet',
            'curr_code' => 'currency',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
