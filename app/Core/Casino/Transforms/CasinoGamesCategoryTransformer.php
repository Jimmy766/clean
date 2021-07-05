<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoGamesCategory;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CasinoGamesCategory",
 *     required={"popular","game"},
 *     @SWG\Property(
 *       property="is_popular",
 *       type="integer",
 *       description="Popular Game",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="game",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/CasinoGame"),
 *       }
 *     ),
 *   )
 */

class CasinoGamesCategoryTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoGamesCategory $casinoGamesCategory){
        return [
            //'order' => (integer)$casinoGamesCategory->order,
            'is_popular' => (integer)$casinoGamesCategory->popular_game,
            'game' => $casinoGamesCategory->casino_game_attributes,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'order' => 'order',
            'popular' => 'popular_game'
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
            'order' => 'order',
            'popular_game' => 'popular'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
