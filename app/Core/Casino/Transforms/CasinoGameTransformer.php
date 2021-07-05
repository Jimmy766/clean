<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoGame;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CasinoGame",
 *     required={"identifier","tag_new","tag_hot","lines","multiplier","blocked","flash","description"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="6"
 *     ),
 *     @SWG\Property(
 *       property="is_new",
 *       type="integer",
 *       description="Tag of New Game",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="is_hot",
 *       type="integer",
 *       description="Tag of Hot Game",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="is_blocked",
 *       type="integer",
 *       description="Blocked Game",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="is_flash",
 *       type="integer",
 *       description="Flash Game",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="demo_url",
 *       type="string",
 *       description="URL to play the Demo",
 *       example="https://www.domain.com/games/?id=6&game_mode=demo"
 *     ),
 *     @SWG\Property(
 *       property="real_play_url",
 *       type="string",
 *       description="URL to play the Game",
 *       example="https://www.domain.com/games/?id=6&game_mode=real_play"
 *     ),
 *     @SWG\Property(
 *       property="description",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/CasinoGamesDescription"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="provider",
 *       type="integer",
 *       description="Provider id (1=MultiSlot, 2=Oryx, 3=RedTiger)",
 *       example="1"
 *     ),
 *   )
 */

class CasinoGameTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoGame $casinoGame) {
        return [
            'identifier' => (integer)$casinoGame->id,
            'is_new' => (integer)$casinoGame->game_new,
            'is_hot' => (integer)$casinoGame->game_hot,
            'is_blocked' => (integer)$casinoGame->game_blocked,
            'is_flash' => (integer)$casinoGame->is_flash,
            'demo_url' => (string)$casinoGame->demo_url,
            'real_play_url' => (string)$casinoGame->real_play_url,
            'description' => $casinoGame->description_attributes,
            'provider' => $casinoGame->casino_provider_id,
            'is_lobby' => $casinoGame->is_lobby,
            'live' => $casinoGame->live,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'provider' => 'casino_provider_id'
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
            'casino_provider_id' => 'provider'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
