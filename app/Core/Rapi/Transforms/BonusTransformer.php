<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Bonus;
use League\Fractal\TransformerAbstract;

class BonusTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Bonus $bonus) {
        return [
            'identifier' => (integer)$bonus->id,
            'source' => (integer)$bonus->source,
            'description' => $bonus->description,
            'products' => $bonus->bonus_products,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'source' => 'source',
            'description' => 'description',
            'products' => 'bonus_products'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'source' => 'source',
            'description' => 'description',
            'bonus_products' => 'products',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
