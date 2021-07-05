<?php

namespace App\Core\Casino\Controllers;

use App\Core\Base\Traits\CacheRedisTraits;
use App\Core\Casino\Services\AddExtraElementsFavoriteableService;
use App\Core\Casino\Services\AllFavoritesAvailableService;
use App\Core\Casino\Services\SetModelFavoritableService;
use App\Http\Controllers\Controller;
use Exception;
use App\Core\Casino\Models\Favorite;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Core\Base\Services\LogType;
use Illuminate\Http\JsonResponse;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Core\Casino\Resources\FavoriteResource;
use App\Core\Casino\Requests\StoreFavoriteRequest;
use App\Core\Casino\Collections\FavoriteCollection;
use Swagger\Annotations as SWG;

class FavoriteController extends Controller
{
    use ApiResponser;
    use CacheRedisTraits;

    /**
     * @var \App\Core\Casino\Services\SetModelFavoritableService
     */
    private $setModelFavoritableService;
    /**
     * @var \App\Core\Casino\Services\AddExtraElementsFavoriteableService
     */
    private $addExtraElementsFavoritableService;
    /**
     * @var AllFavoritesAvailableService
     */
    private $allFavoritesAvailableService;

    public function __construct(
        SetModelFavoritableService $setModelFavoritableService,
        AddExtraElementsFavoriteableService $addExtraElementsFavoritableService,
        AllFavoritesAvailableService $allFavoritesAvailableService
    ) {
        $this->middleware('auth:api');
        $this->setModelFavoritableService         = $setModelFavoritableService;
        $this->addExtraElementsFavoritableService = $addExtraElementsFavoritableService;
        $this->allFavoritesAvailableService = $allFavoritesAvailableService;
    }

    /**
     * @SWG\Get(
     *   path="/favorites",
     *   summary="Show favorite list paginate ",
     *   tags={"Favorite"},
     *   @SWG\Parameter(
     *     name="page_pagination",
     *     in="query",
     *     description="page pagination",
     *     type="integer",
     *     default=1
     *   ),
     *   @SWG\Parameter(
     *     name="size_pagination",
     *     in="query",
     *     description="size pagination",
     *     type="integer",
     *     default=15
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Favorite")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index(Request $request)
    {
        $idUser    = Auth::id();
        $favorites = Favorite::query()
            ->where('id_user', $idUser)
            ->with([ 'favoriteable' ])
            ->paginateByRequest();

        $favoritesItems = collect($favorites->items());

        $favoritesItems = $this->addExtraElementsFavoritableService->execute
    ($favoritesItems);

        $favorites->setCollection($favoritesItems);

        $favoritesCollection = new FavoriteCollection($favorites);

        $data[ 'favorites' ] = $favoritesCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Get(
     *   path="/favorites/list",
     *   summary="Show favorite list ",
     *   tags={"Favorite"},
     *   @SWG\Parameter(
     *     name="type_favorite",
     *     in="query",
     *     description="type favorite entity",
     *     required=false,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Favorite")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function listFavoritesByUser(Request $request)
    {
        $favorites = $this->allFavoritesAvailableService->execute($request);

        $favoritesCollection = FavoriteResource::collection($favorites);

        $data[ 'favorites' ] = $favoritesCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Post(
     *   path="/favorites",
     *   summary="Store new record on favorites ",
     *   tags={"Favorite"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="id_favoriteable",
     *     in="formData",
     *     description="id favoritable entity",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="type_favorite",
     *     in="formData",
     *     description="type Favorite",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Favorite")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store(StoreFavoriteRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $favorite = new Favorite();
            $favorite->fill($request->validated());

            $idUser    = Auth::id();
            $favorite->id_user = $idUser;

            $favorite = $this->setModelFavoritableService->execute($favorite);

            $favorite->save();
            DB::commit();

            $favorite = $favorite->load([ 'favoriteable' ]);


            $tag = Favorite::KEY_CACHE_MODEL."".$idUser;
            $this->forgetCacheByTag($tag);
            $data[ 'favorite' ] = new FavoriteResource($favorite);

            return $this->successResponseWithMessage($data, $successMessage, Response::HTTP_CREATED);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @SWG\Get(
     *   path="/favorites/{id_favorite}",
     *   summary="Show specific favorite ",
     *   tags={"Favorite"},
     *   @SWG\Parameter(
     *     name="id_favorite",
     *     in="path",
     *     description="Favorite ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Favorite")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Favorite $favorite
     * @return JsonResponse
     */
    public function show(Favorite $favorite)
    {
        $idUser = Auth::id();

        $responseFavorite = Favorite::query()
            ->where('id_user', $idUser)
            ->where('id_favorite', $favorite->id_favorite)
            ->with([ 'favoriteable' ])
            ->firstOrFail();

        $data[ 'favorite' ] = new FavoriteResource($responseFavorite);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/favorites/{id_favorite}",
     *   summary="Update record on favorites ",
     *   tags={"Favorite"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="id_favorite",
     *     in="path",
     *     description="Favorite ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="id_favoriteable",
     *     in="formData",
     *     description="id favoritable entity",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="type_favorite",
     *     in="formData",
     *     description="type Favorite",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Favorite")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Casino\Requests\StoreFavoriteRequest $request
     * @param \App\Core\Casino\Models\Favorite               $favorite
     * @return JsonResponse
     */
    public function update(Favorite $favorite, StoreFavoriteRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        $idUser = Auth::id();

        $responseFavorite = Favorite::query()
            ->where('id_user', $idUser)
            ->where('id_favorite', $favorite->id_favorite)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $responseFavorite->fill($request->validated());
            $responseFavorite = $this->setModelFavoritableService->execute($responseFavorite);
            $responseFavorite->save();
            DB::commit();
            $tag = Favorite::KEY_CACHE_MODEL."".$idUser;
            $this->forgetCacheByTag($tag);
            $responseFavorite   = $responseFavorite->load([ 'favoriteable' ]);
            $data[ 'favorite' ] = new FavoriteResource($responseFavorite);

            return $this->successResponseWithMessage($data, $successMessage);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @SWG\Delete(
     *   path="/favorites/{id_favorite}",
     *   summary="Deleted specific favorite ",
     *   tags={"Favorite"},
     *   @SWG\Parameter(
     *     name="id_favorite",
     *     in="path",
     *     description="Favorite ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Favorite")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error",
     *                                       example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code",
     *                                      example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy(Favorite $favorite)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        $idUser = Auth::id();

        $responseFavorite = Favorite::query()
            ->where('id_user', $idUser)
            ->where('id_favorite', $favorite->id_favorite)
            ->with([ 'favoriteable' ])
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $responseFavorite->delete();
            DB::commit();
            $tag = Favorite::KEY_CACHE_MODEL."".$idUser;
            $this->forgetCacheByTag($tag);
            $data[ 'favorite' ] = new FavoriteResource($responseFavorite);

            return $this->successResponseWithMessage($data, $successMessage);

        }
        catch(Exception $exception) {
            DB::rollBack();

            LogType::error(__FILE__, __LINE__, $errorMessage, [
                'exception' => $exception,
                'usersId'   => Auth::id(),
            ]);
            return $this->errorCatchResponse($exception, $errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
