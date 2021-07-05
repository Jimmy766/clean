<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Price;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryPrice",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Price identifier",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       type="integer",
 *       description="Draws quantity",
 *       example="123",
 *     ),
 *     @SWG\Property(
 *       property="time",
 *       type="integer",
 *       description="Time",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="time_type",
 *       type="integer",
 *       description="Time type",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="min_jackpot",
 *       type="integer",
 *       description="Min jackpot",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="model",
 *       type="integer",
 *       description="Model",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Price",
 *       example="23.45",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="EUR",
 *       example="23.45",
 *     ),
 *  ),
 */

class PriceTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Price $price) {
        $price_line = $price->price_line;
        return [
            'identifier' => (integer)$price->prc_id,
            'draws' => $price->prc_draws,
            'time' => (integer)$price->prc_time,
            'time_type' => $price->time_type,
            //'min_tickets' => (integer)$price->prc_min_tickets,
            'min_jackpot' => (integer)$price->prc_min_jackpot,
            'model' => (integer)$price->prc_model_type,
            'days_by_tickets' => $price->prc_days_by_tickets,
            'price' => round((float)$price_line['price'],2),
            'currency' => $price_line['currency'],
            'prices_lines' => $price->prices_lines_attributes,
            'price_modifier_1' => round((float)$price_line['modifier1'], 2),
            'price_modifier_2' => round((float)$price_line['modifier2'], 2),
            'price_modifier_3' => round((float)$price_line['modifier3'], 2),
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'prc_id',
            'draws' => 'prc_draws',
            'time' => 'prc_time',
            'time_type' => 'prc_time_type',
            'min_tickets' => 'prc_min_tickets',
            'min_jackpot' => 'prc_min_jackpot',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'prc_id' => 'identifier',
            'prc_draws' => 'draws',
            'prc_time' => 'time',
            'prc_time_type' => 'time_type',
            'prc_min_tickets' => 'min_tickets',
            'prc_min_jackpot' => 'min_jackpot',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
