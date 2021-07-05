<?php

namespace App\Core\ScratchCards\Transforms;

use App\Core\ScratchCards\Models\ScratchCardPriceLine;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="ScratchCardPriceLine",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="integer",
 *       description="Price of scratch card",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *   ),
 */

class ScratchCardPriceLineTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(ScratchCardPriceLine $scratch_price_line) {
        return [
            'identifier' => (integer)$scratch_price_line->prcln_id,
            'price' => $scratch_price_line->prcln_price,
            'currency' => (string)$scratch_price_line->curr_code,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'prcln_id',
            'price' => 'prcln_price',
            'currency' => 'curr_code',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function transformedAttribute($index) {
        $attributes = [
            'identifier' => 'prcln_id',
            'prcln_price' => 'price',
            'curr_code' => 'currency',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
