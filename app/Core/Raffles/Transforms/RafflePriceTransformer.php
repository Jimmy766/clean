<?php

namespace App\Core\Raffles\Transforms;

use App\Core\Raffles\Models\RafflePrice;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="RafflePrice",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Raffle identifier",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="time",
 *       type="integer",
 *       description="Time",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="min_tickets",
 *       type="integer",
 *       description="Min tickets",
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
 *       description="Currency",
 *       example="EUR",
 *     ),
 *  ),
 */

class RafflePriceTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(RafflePrice $raffle_price) {
        $price_line = $raffle_price->price_line;
        return [
            'identifier' => (integer)$raffle_price->prc_rff_id,
            'time' => $raffle_price->prc_rff_time,
            'min_tickets' => $raffle_price->prc_rff_min_tickets,
            'price' => round((float)$price_line['price'],2),
            'currency' => $price_line['currency'],
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'prc_rff_id',
            'time' => 'prc_rff_time',
            'min_tickets' => 'prc_rff_min_tickets',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'prc_rff_id' => 'identifier',
            'prc_rff_time' => 'time',
            'prc_rff_min_tickets' => 'min_tickets',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
