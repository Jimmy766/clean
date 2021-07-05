<?php

namespace App\Core\Languages\Controllers;

use App\Core\Countries\Services\FormatCountriesToCreateRegionService;
use App\Core\Countries\Services\GetCountriesService;
use App\Http\Controllers\Controller;
use Exception;
use App\Core\Terms\Models\Language;
use App\Core\Base\Traits\ApiResponser;
use Illuminate\Http\Response;
use App\Core\Base\Services\LogType;
use Illuminate\Http\JsonResponse;
use DB;
use Illuminate\Support\Facades\Auth;
use App\Core\Languages\Resources\LanguageResource;
use App\Core\Terms\Requests\StoreLanguageRequest;
use App\Core\Languages\Collections\LanguageCollection;

class LanguageController extends Controller
{
    use ApiResponser;

    /**
     * @var \App\Core\Countries\Services\GetCountriesService
     */
    private $getCountriesService;
    /**
     * @var \App\Core\Countries\Services\FormatCountriesToCreateRegionService
     */
    private $formatCountriesToCreateRegionService;

    public function __construct(GetCountriesService $getCountriesService,
                                FormatCountriesToCreateRegionService $formatCountriesToCreateRegionService) {
        $this->middleware('client.credentials');
        $this->middleware('check.external_access');
        $this->getCountriesService = $getCountriesService;
        $this->formatCountriesToCreateRegionService = $formatCountriesToCreateRegionService;
    }


    /**
     * @SWG\Get(
     *   path="/languages",
     *   summary="Show language list ",
     *   tags={"Language"},
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Language")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index()
    {
        $languages = Language::query()->paginateByRequest();

        $languagesCollection = new LanguageCollection($languages);

        $data['languages'] = $languagesCollection;

        return $this->successResponseWithMessage($data);
    }

    public function create()
    {
        $countries = $this->getCountriesService->execute();
        $countries = $this->formatCountriesToCreateRegionService->execute($countries);

        $data['countries'] = $countries;
        return $this->successResponseWithMessage($data);


    }


    /**
     * @SWG\Post(
     *   path="/languages",
     *   summary="Store new record on languages ",
     *   tags={"Language"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="Name Language",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="code",
     *     in="formData",
     *     description="Code Language",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Language")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store(StoreLanguageRequest $request)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $language = new Language();
            $language->fill($request->validated());
            $language->save();
            DB::commit();

            $data['language'] = $language;

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
     *   path="/languages/{language_id}",
     *   summary="Show specific language ",
     *   tags={"Language"},
     *   @SWG\Parameter(
     *     name="language_id",
     *     in="path",
     *     description="Language ID",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Language")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Terms\Models\Language $language
     * @return JsonResponse
     */
    public function show(Language $language)
    {
        $data['language'] = new LanguageResource($language);

        return $this->successResponseWithMessage($data);
    }


    /**
     * @SWG\Put(
     *   path="/languages/{language_id}",
     *   summary="Update record on languages ",
     *   tags={"Language"},
     *   consumes={"application/x-www-form-urlencoded"},
     *   @SWG\Parameter(
     *     name="language_id",
     *     in="path",
     *     description="Language ID",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     description="Name Language",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="code",
     *     in="formData",
     *     description="Code Language",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Language")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param \App\Core\Terms\Requests\StoreLanguageRequest $request
     * @param \App\Core\Terms\Models\Language               $language
     * @return JsonResponse
     */
    public function update(Language $language, StoreLanguageRequest $request)
    {
        $successMessage = __('Successful update');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $language->fill($request->validated());
            $language->save();
            DB::commit();
            $data['language'] = new LanguageResource($language);

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
     *   path="/languages/{language_id}",
     *   summary="Deleted specific language ",
     *   tags={"Language"},
     *   @SWG\Parameter(
     *     name="language_id",
     *     in="path",
     *     description="Language ID",
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
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Language")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(
     *     response="403",
     *     description="Forbidden Access",
     *     @SWG\Schema(
     *       @SWG\Property(property="error", type="string", description="Message error", example="This data is not allowed for you"),
     *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
     *     ),
     *   ),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy(Language $language)
    {
        $successMessage = __('Operation Successful');
        $errorMessage   = __('An error has occurred');

        try {
            DB::beginTransaction();

            $language->delete();
            DB::commit();
            $data['language'] = new LanguageResource($language);

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
