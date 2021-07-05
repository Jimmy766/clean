<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicateRafflePrice;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicateRafflePrice",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Raffle Syndicate identifier",
 *       example="3",
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
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Price",
 *       example="85",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="EUR",
 *     ),
 *  ),
 */

class SyndicateRafflePriceTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateRafflePrice $syndicate_raffle_price) {
        return [
            'identifier' => (integer)$syndicate_raffle_price->prc_id,
            'time' => (string)$syndicate_raffle_price->prc_time,
            'time_type' => $syndicate_raffle_price->prc_time_type,
            'price' => round((float)$syndicate_raffle_price->price,2),
            'currency' => $syndicate_raffle_price->currency,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'prc_id',
            'time' => 'prc_time',
            'time_type' => 'prc_time_type',
            'share_cost' => 'prc_share_cost',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'prc_id' => 'identifier',
            'prc_time' => 'time',
            'prc_time_type' => 'time_type',
            'prc_share_cost' => 'share_cost',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
