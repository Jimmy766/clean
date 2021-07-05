<?php

namespace App\Core\Assets\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use App\Core\Assets\Models\Asset;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Core\Base\Services\LogType;
use Illuminate\Http\JsonResponse;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Core\Assets\Resources\AssetResource;
use App\Core\Assets\Requests\StoreAssetRequest;
use App\Core\Assets\Collections\AssetCollection;

/**
 * Class AssetController
 * @package App\Http\Controllers
 */
class AssetController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access');
    }

    /**
     * @SWG\Get(
     *   path="/assets",
     *   summary="Show asset list ",
     *   tags={"Asset"},
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Asset")),
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
        $assets = Asset::paginateByRequest();

        $assetsCollection = new AssetCollection($assets);

        $data[ 'assets' ] = $assetsCollection;

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Post(
     *   path="/assets",
     *   summary="Store new record on assets ",
     *   tags={"Asset"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="Name Asset",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="image",
     *     in="formData",
     *     description="Image Asset",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Asset")),
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
    public function store(StoreAssetRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $asset = new Asset();
            $asset->fill($request->validated());
            $asset->save();
            DB::commit();

            $data[ 'asset' ] = new AssetResource($asset);

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
     *   path="/assets/{id_asset}",
     *   summary="Show specific asset ",
     *   tags={"Asset"},
     *   @SWG\Parameter(
     *     name="id_asset",
     *     in="path",
     *     description="Asset ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Asset")),
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
     * @param Asset $asset
     * @return JsonResponse
     */
    public function show(Asset $asset)
    {
        $data[ 'asset' ] =  new AssetResource($asset);

        return $this->successResponseWithMessage($data);
    }

    /**
     * @SWG\Put(
     *   path="/assets/{id_asset}",
     *   summary="Update record on assets ",
     *   tags={"Asset"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="id_asset",
     *     in="path",
     *     description="Asset ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="Name Asset",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="image",
     *     in="formData",
     *     description="Image Asset",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Asset")),
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
     * @param StoreAssetRequest             $request
     * @param \App\Core\Assets\Models\Asset $asset
     * @return JsonResponse
     */
    public function update(Asset $asset, StoreAssetRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $asset->fill($request->validated());
            $asset->save();
            DB::commit();
            $data[ 'asset' ] =  new AssetResource($asset);

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
     *   path="/assets/{id_asset}",
     *   summary="Deleted specific asset ",
     *   tags={"Asset"},
     *   @SWG\Parameter(
     *     name="id_asset",
     *     in="path",
     *     description="Asset ID",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Asset")),
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
    public function destroy(Asset $asset)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $asset->delete();
            DB::commit();
            $data[ 'asset' ] =  new AssetResource($asset);

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
