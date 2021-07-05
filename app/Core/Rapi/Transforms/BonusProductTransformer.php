<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Bonus\Models\BonusProduct;
use League\Fractal\TransformerAbstract;

class BonusProductTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(BonusProduct $bonus_product) {
        return [
            'identifier' => (integer)$bonus_product->id,
            'tag' => (string)$bonus_product->bonus_tag,
            'type' => (integer)$bonus_product->product_type,
            'product_identifier' => (integer)$bonus_product->product_id,
            'quantity' => (integer)$bonus_product->product_quantity,
            //'product' => $bonus_product->product_detail,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'tag' => 'bonus_tag',
            'type' => 'product_type',
            'product_identifier' => 'product_id',
            'quantity' => 'product_quantity',
            'price_id' => 'prc_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'bonus_tag' => 'tag',
            'product_type' => 'type',
            'product_id' => 'product_identifier',
            'product_quantity' => 'quantity',
            'prc_id' => 'price_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
