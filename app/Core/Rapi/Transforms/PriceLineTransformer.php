<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\PriceLine;
use League\Fractal\TransformerAbstract;


/**
 * @SWG\Definition(
 *     definition="Price Line",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Price line identifier",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Price line price",
 *       example="33.3",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Price line currency",
 *       example="USD",
 *     ),
 *     @SWG\Property(
 *       property="discount",
 *       type="number",
 *       format="float",
 *       description="Discount",
 *       example="3.1",
 *     ),
 *     @SWG\Property(
 *       property="modifier1",
 *       type="number",
 *       format="float",
 *       description="Modifier 1",
 *       example="1.2",
 *     ),
 *     @SWG\Property(
 *       property="modifier2",
 *       type="number",
 *       format="float",
 *       description="Modifier 2",
 *       example="1.2",
 *     ),
 *     @SWG\Property(
 *       property="modifier3",
 *       type="number",
 *       format="float",
 *       description="Modifier 3",
 *       example="1.2",
 *     ),
 *  ),
 */

class PriceLineTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(PriceLine $price_line) {
        return [
            'identifier' => (integer)$price_line->prcln_id,
            'price' => $price_line->prcln_price,
            'currency' => (string)$price_line->curr_code,
            'discount' => (integer)$price_line->prcln_discount,
            'modifier1' => $price_line->price_modifier_1,
            'modifier2' => $price_line->price_modifier_2,
            'modifier3' => $price_line->price_modifier_3,
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
            'discount' => 'prcln_discount',
            'modifier1' => 'price_modifier_1',
            'modifier2' => 'price_modifier_2',
            'modifier3' => 'price_modifier_3',
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
            'prcln_discount' => 'discount',
            'price_modifier_1' => 'modifier1',
            'price_modifier_2' => 'modifier2',
            'price_modifier_3' => 'modifier3',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
