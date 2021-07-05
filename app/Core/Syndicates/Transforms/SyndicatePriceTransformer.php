<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicatePrice;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicatePrice",
 *     required={"pick_balls","extra_balls","refund_balls"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Price identifier",
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
 *       example="#MONTH#",
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       type="integer",
 *       description="Draws quantity",
 *       example="123",
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="integer",
 *       description="Price",
 *       example="25",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="EUR",
 *     ),
 *  ),
 */

class SyndicatePriceTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicatePrice $syndicate_price) {
        $syndicate_price_line = $syndicate_price->syndicate_price_line;
        return [
            'identifier' => (integer)$syndicate_price->prc_id,
            'time' => (string)$syndicate_price->prc_time,
            'time_type' => $syndicate_price->time_type,
            'draws' => $syndicate_price->draws,
            'price' => round((float)$syndicate_price_line['price'],2),
            'currency' => $syndicate_price_line['currency'],
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
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'prc_id' => 'identifier',
            'prc_time' => 'time',
            'prc_time_type' => 'time_type',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
