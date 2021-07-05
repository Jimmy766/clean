<?php

namespace App\Core\Raffles\Transforms;

use App\Core\Raffles\Models\RafflePriceLine;
use League\Fractal\TransformerAbstract;

class RafflePriceLineTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(RafflePriceLine $raffle_price_line) {
        return [
            'identifier' => (integer)$raffle_price_line->prcln_rff_id,
            'price' => $raffle_price_line->prcln_rff_price,
            'currency' => $raffle_price_line->curr_code,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'prcln_id',
            'price' => 'prcln_rff_price',
            'price_mx' => 'prcln_rff_price_mx',
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
            'identifier' => 'prcln_rff_id',
            'prcln_rff_price' => 'price',
            'prcln_rff_price_mx' => 'price_mx',
            'curr_code' => 'currency',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
