<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Deal;
use League\Fractal\TransformerAbstract;


class DealTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Deal $deal) {
        return [
            'identifier' => (integer)$deal->id,
            'deal_promo_type' => (integer)$deal->deal_promo_type,
            'deal_promo_value' => (integer)$deal->deal_promo_value,
            'deal_uses' => (integer)$deal->deal_uses,
            'deal_max_uses' => (integer)$deal->deal_max_uses,
            'deal_tag' => (string)$deal->tag,
            'promotion' => $deal->promotion_attributes,

        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'promo_type' => 'deal_promo_type',
            'promo_value' => 'deal_promo_value',
            'uses' => 'deal_uses',
            'max_uses' => 'deal_max_uses',
            'tag' => 'deal_tag',
            'logo' => 'deal_logo',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'deal_promo_type' => 'promo_type',
            'deal_promo_value' => 'promo_value',
            'deal_uses' => 'uses',
            'deal_max_uses' => 'max_uses',
            'deal_tag' => 'tag',
            'deal_logo' => 'logo',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
