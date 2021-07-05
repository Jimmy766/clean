<?php

namespace App\Core\SportBooks\Controllers;

use App\Core\Base\Classes\ModelConst;
use App\Core\SportBooks\Collections\SportBooksGameCollection;
use App\Core\SportBooks\Requests\GetGameSportBookRequest;
use App\Core\SportBooks\Requests\GetGameSportBooksRequest;
use App\Core\Countries\Services\CheckCountryAndStateBlocksService;
use App\Core\Users\Services\GetBalanceUserService;
use App\Core\SportBooks\Services\SearchConfigSportBooksAndGetUrlService;
use App\Core\SportBooks\Models\SportBooksGame;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SportBooksGameController extends ApiController
{

    use ApiResponser;

    /**
     * @var \App\Core\Users\Services\GetBalanceUserService
     */
    private $getBalanceUserService;
    /**
     * @var SearchConfigSportBooksAndGetUrlService
     */
    private $searchConfigSportBooksAndGetUrlService;

    public function __construct(
        GetBalanceUserService $getBalanceUserService,
        SearchConfigSportBooksAndGetUrlService $searchConfigSportBooksAndGetUrlService
    ) {
        parent::__construct();
        $this->middleware('auth:api')->only(['gameSportBooksGetUrlIframe', 'gameSportBooksGetUrlIframeBets']);
        $this->middleware('client.credentials');
        $this->getBalanceUserService                  = $getBalanceUserService;
        $this->searchConfigSportBooksAndGetUrlService = $searchConfigSportBooksAndGetUrlService;
    }

    /**
     * @SWG\Get(
     *   path="/games/sport-books/list",
     *   summary="get list game sport-book",
     *   tags={"Games"},
     *   consumes={"application/json"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Parameter(
     *     name="language",
     *     in="query",
     *     description="language",
     *     type="string",
     *     default="es"
     *   ),
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/sportBookGame")),
     *     ),
     *   ),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     */
    public function index(GetGameSportBooksRequest $request)
    {
        $idsProducts = self::client_sport_books_games(1)->pluck('product_id');

        $games = SportBooksGame::query()
            ->whereIn('id', $idsProducts)
            ->paginateFromCacheByRequest();

        $listExcept = ModelConst::EXCEPT_COUNTRY_REGION_CASINO_SPORT_SCRATCH;
        $exceptCountryState = collect($listExcept);

        $games = CheckCountryAndStateBlocksService::execute($exceptCountryState, $games, 1);

        $gamesCollection    = new SportBooksGameCollection($games);
        $data[ 'games' ]    = $gamesCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/games/sport-books/generate-url-iframe/{id_game}/",
     *   summary="get game sport-book with url iframe",
     *   tags={"Games"},
     *   consumes={"application/json"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Parameter(
     *     name="id_game",
     *     in="path",
     *     description="id_game",
     *     required=true,
     *     type="string"
     *   ),
     *
     *   @SWG\Parameter(
     *     name="language",
     *     in="query",
     *     description="language",
     *     type="string",
     *     default="es"
     *   ),
     *
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param GetGameSportBookRequest $request
     * @param                         $id_game
     * @return JsonResponse
     */
    public function gameSportBooksGetUrlIframe(
        GetGameSportBookRequest $request,
        $id_game
    ) {
        $sportBook = SportBooksGame::query()->where(
            'id',
            $id_game
        )->firstFromCache();

        if($sportBook === null){
            throw new UnprocessableEntityHttpException(__('sport-book dont exist'), null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $site = $request->client_site_id;
        $urlToIframe = $this->searchConfigSportBooksAndGetUrlService->execute(
            $sportBook, $request->language, null, $site
        );

        return $this->successResponse(['url'=>$urlToIframe]);

    }


    /**
     * @SWG\Get(
     *   path="/games/sport-books/generate-url-iframe-bets/{id_game}/",
     *   summary="get game sport-book with url iframe bets",
     *   tags={"Games"},
     *   consumes={"application/json"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Parameter(
     *     name="id_game",
     *     in="path",
     *     description="id_game",
     *     required=true,
     *     type="string"
     *   ),
     *
     *   @SWG\Parameter(
     *     name="language",
     *     in="query",
     *     description="language",
     *     type="string",
     *     default="es"
     *   ),
     *
     *   @SWG\Response(response=200, ref="#/responses/200"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param GetGameSportBookRequest $request
     * @param                         $id_game
     * @return JsonResponse
     */
    public function gameSportBooksGetUrlIframeBets(
        GetGameSportBookRequest $request,
        $id_game
    ) {
        $sportBook = SportBooksGame::query()->where(
            'id',
            $id_game
        )->firstFromCache();

        if($sportBook === null){
            throw new UnprocessableEntityHttpException(__('sport-book dont exist'), null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $site = $request->client_site_id;
        $urlToIframe = $this->searchConfigSportBooksAndGetUrlService->execute(
            $sportBook, $request->language, 'REQUEST_URL_BETS', $site
        );

        return $this->successResponse(['url'=>$urlToIframe]);

    }
}

