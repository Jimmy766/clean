<?php

namespace App\Core\SportBooks\Resources;

use App\Core\Cloud\Services\GetCloudUrlService;
use App\Core\Base\Services\GetOriginRequestService;
use App\Core\Cloud\Services\SetOriginCloudUrlService;
use App\Core\SportBooks\Models\SportBooksGame;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="sportBookGame",
 *     required={"identifier","tag_new","tag_hot","lines","multiplier","blocked","flash","description"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="6"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="name game",
 *       example="name game"
 *     ),
 *     @SWG\Property(
 *       property="text",
 *       type="string",
 *       description="text",
 *       example="description game"
 *     ),
 *     @SWG\Property(
 *       property="active",
 *       type="integer",
 *       description="is active game",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="live",
 *       type="integer",
 *       description="live",
 *       example=0
 *     ),
 *     @SWG\Property(
 *       property="is_lobby",
 *       type="integer",
 *       description="is_lobby",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="game_code",
 *       type="integer",
 *       description="game_code",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="game_rtp",
 *       type="integer",
 *       description="game_rtp",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="game_enable",
 *       type="integer",
 *       description="game_enable",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="multiplier",
 *       type="integer",
 *       description="multiplier",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="real_play_url",
 *       type="string",
 *       description="URL to play the Game",
 *       example="https://www.domain.com/games/?id=6&game_mode=real_play"
 *     ),
 *   )
 * */
class SportBookGameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $language = $request->language !== null ? $request->language : 'en';
        return [
            'identifier'    => $this->id,
            'name'          => $this->name,
            'text'          => $this->text,
            'active'        => $this->active,
            'live'          => $this->live,
            'is_lobby'      => $this->is_lobby,
            'game_code'     => $this->game_code,
            'game_rtp'      => $this->game_rtp,
            'game_enable'   => $this->game_enable,
            'multiplier'    => $this->multiplier,
            'demo_url'      => SportBooksGame::getUrlDemoDefault($language),
            'real_play_url' => $this->makeUrlCloudRealPlayUrl($this->id),
            'bets_url' => $this->makeUrlCloudRealPlayUrl($this->id, 1),
        ];
    }

    public function makeUrlCloudRealPlayUrl($id, $bets = null)
    {
        $token    = request()->header('authorization');
        $token    = explode(" ", $token);
        $token    = $token[ 1 ];
        $token    = base64_encode($token);
        $ip       = request()->user_ip;
        $lang     = request()->language;
        $cloudUrl = GetCloudUrlService::execute();

        $url = "{$cloudUrl}/games/?id={$id}&lang={$lang}&game_mode=real_play&user_ip={$ip}&t={$token}&type=1";
        $url = is_null($bets) ? $url."&type=sportbook" : $url."&type=sportbook_bets";
        $url = SetOriginCloudUrlService::execute($url);
        return request()->user_id
            ? $url
            : '';
    }
}
