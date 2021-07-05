<?php

namespace App\Core\Casino\Resources;

use App\Core\Casino\Resources\CasinoCategoryResource;
use App\Core\Casino\Resources\CasinoGameBetConfigResource;
use App\Core\Casino\Resources\CasinoGameDescriptionResource;
use App\Core\Casino\Resources\CasinoProviderResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CasinoGameResource extends JsonResource
{


    /**
     *   @SWG\Definition(
     *     definition="CasinoGameResponse",
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
     *       property="is_popular",
     *       type="integer",
     *       description="Popular Game",
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
     *       type="array",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/CasinoGamesDescription"),
     *          }
     *        )
     *     ),
     *     @SWG\Property(
     *       property="provider",
     *       type="object",
     *       description="Casino Provider",
     *       ref="#/definitions/CasinoProvider",
     *     ),
     *     @SWG\Property(
     *       property="category",
     *       type="object",
     *       description="Casino Category",
     *       ref="#/definitions/CasinoCategoryResponse"
     *     ),
     *     @SWG\Property(
     *       property="bet_config",
     *       description="Casino Bet Config",
     *       type="array",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/CasinoGamesBetConfig"),
     *          }
     *        )
     *     ),
     *   )
     */

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'identifier' => (integer)$this->id,
            'is_popular' =>$this->when($this->relationLoaded('casino_games_category_clients'),(integer)$this->casino_games_category_clients->popular_game),
            'is_new' => (integer)$this->game_new,
            'is_hot' => (integer)$this->game_hot,
            'is_blocked' => (integer)$this->game_blocked,
            'is_flash' => (integer)$this->is_flash,
            'demo_url' => (string)$this->demo_url,
            'real_play_url' => (string)$this->real_play_url,
            'description' => CasinoGameDescriptionResource::collection($this->whenLoaded('description')),
            'provider' => new CasinoProviderResource($this->whenLoaded('provider')),
            'is_lobby' => $this->is_lobby,
            'live' => $this->live,
            'category'=> $this->whenLoaded('casino_games_category_clients',function(){
                    return $this->when($this->casino_games_category_clients->relationLoaded('casino_category'),
                        new CasinoCategoryResource($this->casino_games_category_clients->casino_category));
                }),
            'bet_config'=>CasinoGameBetConfigResource::collection($this->whenLoaded('casino_games_bet_config'))
        ];
    }
}
