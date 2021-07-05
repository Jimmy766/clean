<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\Syndicates\Models\SyndicatePriceLine;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="SyndicatePriceLine",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Syndicate Price line identifier",
 *       example="3",
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Syndicate price line price",
 *       example="33.3",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Syndicate Price line currency",
 *       example="USD",
 *     ),
 *  ),
 */

class SyndicatePriceLineTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicatePriceLine $syndicate_price_line) {
        return [
            'identifier' => (integer)$syndicate_price_line->prcln_id,
            'price' => $syndicate_price_line->prcln_price,
            'currency' => (string)$syndicate_price_line->curr_code,
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
