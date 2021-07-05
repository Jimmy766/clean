<?php

namespace App\Core\Casino\Controllers;

use App\Core\Casino\Models\CasinoGame;
use App\Core\Casino\Models\CasinoGamesToken;
use App\Core\Casino\Models\CasinoGamesTransaction;
use App\Core\Casino\Models\CasinoProvider;
use App\Core\Casino\Models\CasinoProviderConfig;
use App\Core\Clients\Models\Client;
use App\Core\Casino\Services\CasinoIntegration;
use App\Core\Rapi\Services\Log;
use App\Core\Casino\Transforms\CasinoGameTransformer;
use App\Core\Users\Models\User;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client as HttpClient;


class CasinoGameController extends ApiController
{
    const LANGS = [
        'en'=> 'ENG',
        'es'=> 'SPA',
        'pt'=> 'POR',
        'it'=> 'ITA',
        'fr'=> 'FRA',
        'de'=> 'DEU',
        'ru'=> 'RUS',
        'cn'=> 'CHI',
        'tw'=> 'CHI',
        'pl'=> 'POL'
    ];

    public function __construct(){
        parent::__construct();
        $this->middleware('auth:api')->except('show','game_demo_url');
        $this->middleware('client.credentials')->only('show','game_demo_url');
    }

    /**
     * @SWG\Get(
     *   path="/games/{game}",
     *   tags={"Games"},
     *   summary="Show game details",
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="game",
     *     in="path",
     *     description="Game Id.",
     *     required=true,
     *     type="integer",
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/CasinoGame")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Casino\Models\CasinoGame $casinoGame
     * @return \Illuminate\Http\Response
     */
    public function show(CasinoGame $game) {

        if ($game->game_enabled==0 || !$game->provider)
            return $this->errorResponse(trans('lang.no_data'), 404);


        return self::client_casino_games(1)->pluck('product_id')->contains($game->id)?$this->showOne($game):$this->errorResponse(trans('lang.no_data'), 403);
    }

    /*
     *
     * SWG\Get(
     *   path="/games/demo_url/{game}",
     *   tags={"Games"},
     *   summary="Show game url to play the demo",
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *
     *   SWG\Parameter(
     *     name="game",
     *     in="path",
     *     description="Game Id.",
     *     required=true,
     *     type="integer",
     *   ),
     *   SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     SWG\Schema(
     *         SWG\Property(
     *          property="url",
     *          type="string",
     *          description="Demo Game URL",
     *          example="http://some.domain.com/play__demo_game"
     *       ),
     *     ),
     *   ),
     *   SWG\Response(response=401, ref="#/responses/401"),
     *   SWG\Response(response=403, ref="#/responses/403"),
     *   SWG\Response(response=404, ref="#/responses/404"),
     *   SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    /**
     * Display the specified resource.
     *
     * @param \App\Core\Casino\Models\CasinoGame $game
     * @return \Illuminate\Http\JsonResponse
     */
    public function game_demo_url(Request $request,CasinoGame $game) {

        $this->validate($request, ['is_mobile' => 'required|boolean']);

        if (!$game->canOpen())
            return $this->errorResponse(trans('lang.game_forbidden'), 403);

        if ($url = CasinoIntegration::getUrl($game,'demo',$this->getLanguage())){
            return $this->successResponse(['url'=>$url]);
        }else{
            return $this->errorResponse(trans('lang.game_forbidden'), 403);
        }

    }

    /*
     * SWG\Get(
     *   path="/games/real_play_url/{game}",
     *   tags={"Games"},
     *   summary="Show game url to real play",
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   SWG\Parameter(
     *     name="game",
     *     in="path",
     *     description="Game Id.",
     *     required=true,
     *     type="integer",
     *   ),
     *   SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     SWG\Schema(
     *         SWG\Property(
     *          property="url",
     *          type="array",
     *          SWG\Items(
     *              SWG\Property(property="desktop", type="string", description="Destkop URL", example="http://some.domain.com/play_desktop_real_game"),
     *              SWG\Property(property="mobile", type="string", description="Mobile URL", example="http://some.domain.com/play_mobile_real_game"),
     *          )
     *       ),
     *     ),
     *   ),
     *   SWG\Response(response=401, ref="#/responses/401"),
     *   SWG\Response(response=404, ref="#/responses/404"),
     *   SWG\Response(response=422, ref="#/responses/422"),
     *   SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    /**
     * Display the specified resource.
     *
     * @param \App\Core\Casino\Models\CasinoGame $game
     * @return \Illuminate\Http\JsonResponse
     */
    public function game_real_url(Request $request,CasinoGame $game) {
        $this->validate($request, ['is_mobile' => 'required|boolean']);

        if (!$game->canOpen())
            return $this->errorResponse(trans('lang.game_forbidden_1'), 403);
        elseif (CasinoIntegration::canPlay($game)===false)
            return $this->errorResponse(trans('lang.not_balance_to_play'), 403);


        if ($url = CasinoIntegration::getUrl($game,'real',$this->getLanguage())){
            return $this->successResponse(['url'=>$url]);
        }else{
            return $this->errorResponse(trans('lang.game_forbidden_2'), 403);
        }
    }

    /**
     * @SWG\Post(
     *   path="/games/can_play/{game}",
     *   summary="Can play game",
     *   tags={"Games"},
     *   @SWG\Parameter(
     *     name="game",
     *     in="path",
     *     description="Game Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="user_agent",
     *     in="formData",
     *     description="Browser User Agent",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", type="array",
     *          @SWG\Items(
     *            @SWG\Property(property="can_play", type="boolean")
     *          )
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function can_play(Request $request,CasinoGame $game) {
        $this->validate($request, ['user_agent' => 'required|string']);

        $request['is_mobile'] = $this->isMobile();

        return $this->successResponse(['data' => ['can_play' => CasinoIntegration::canPlay($game)]]);
    }
}

