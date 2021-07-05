<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoProvider;
use League\Fractal\TransformerAbstract;

class CasinoProviderTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoProvider $casinoProvider) {
        return [
            'identifier' => (integer)$casinoProvider->id,
            'name' => (string)$casinoProvider->name

        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'name' => 'name'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'name' => 'name'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
