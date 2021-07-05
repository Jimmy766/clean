<?php

namespace App\Core\Rapi\Transforms;

use League\Fractal\TransformerAbstract;



class PriceLineBoostedTransformer extends TransformerAbstract
{
    /**
     * @param $priceLine
     * @return array
     */
    public static function transform($priceLine): array
    {
        return [
            'identifier'     => $priceLine->prcln_id,
            'modifier_id'    => $priceLine->modifier_id,
            'modifier_price' => round((float)$priceLine->modifier_price,2),
            'currency'       => $priceLine->curr_code,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
        ];
        return $attributes[ $index ] ?? null;
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function transformedAttribute($index) {
        $attributes = [
        ];
        return $attributes[ $index ] ?? null;
    }
}
