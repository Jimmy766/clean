<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Deal;
use League\Fractal\TransformerAbstract;


class DealListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(Deal $deal) {
        $result = [
            'identifier' => (integer)$deal->lot_id,
            'tag' => (string)$deal->deal_tag,
            'promotion' => $deal->promotion_attributes,
        ];

        return $result;
    }

    public static function originalAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [

        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
