<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoGamesBonus;
use League\Fractal\TransformerAbstract;

class CasinoGamesBonusTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoGamesBonus $casinoGamesBonus) {
        return [
            'identifier' => (integer)$casinoGamesBonus->id,
            'expiration_date' => $casinoGamesBonus->expiration_date->format('Y-m-d H:i:s'),

            //'active' => (array)$casinoGamesBonus->active_attributes,
            //'pendings' => (array)$casinoGamesBonus->pendings_attributes,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
