<?php

namespace App\Core\Base\Traits;

use App\Core\Base\Services\GetInfoFromExceptionService;
use App\Core\Base\Services\GetOriginRequestService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Services\SendLogUserRequestResponseService;
use App\Core\Clients\Models\ClientProduct;
use App\Core\Rapi\Models\LogConfig;
use Exception;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

trait ApiResponser
{

    public static function client_lotteries($play = 0, $result = 0) {
        return self::client_products(1, $play, $result);
    }


    /**
     * @param     $productTypeId
     * @param int $play
     * @param int $result
     * @return mixed
     */
    public static function client_products($productTypeId, $play = 0, $result = 0)
    {
        $clientId = request()->oauth_client_id;

        $tc  = round(microtime(true) * 1000, 2);

        $origin = GetOriginRequestService::execute();

        $activeExceptionDomain = env('DOMAIN_STATIC_EXCEPTION',null) === $origin;

        $clientProducts = ClientProduct::query()
            ->with('client_product_blacklist', 'client_product_type_country_blacklists')
            ->where('product_type_id', '=', $productTypeId);
        if($activeExceptionDomain === false){
            $clientProducts = $clientProducts->where('oauth_client_id', '=', request()->oauth_client_id);
        }

        if ($result === 1 && $activeExceptionDomain === false) {
            $clientProducts = $clientProducts->where('result', '=', $result);
        }
        if ($play === 1 && $activeExceptionDomain === false) {
            $clientProducts = $clientProducts->where('play', '=', $play);
        }

        $clientProducts = $clientProducts->getFromCache();
        if($activeExceptionDomain === true){
            return $clientProducts;
        }

        $clientProducts = $clientProducts->filter(function (ClientProduct $item) use (
            $play,
            $result
        ) {
            if ($item->client_products_country_blacklist($play, $result)->isEmpty()) {
                return $item;
            }
        });
        return $clientProducts;
    }

    public static function record_log_static2($type, $tag, $array = []) {
        $log = Cache::remember('log_' . $type . '_' . request('user_ip'), config('constants.cache_5'), function () use ($type, $tag) {
            return LogConfig::query()->where('type', '=', $type)->where('active', '=', 1)
                ->where(function ($query) {
                    $query->whereNull('user_ip')->orWhere('user_ip', '=', request('user_ip'));
                })->getFromCache();
        });
        if ($log->isNotEmpty()) {
            $sendLogConsoleService = new SendLogConsoleService();
            $log->each(function ($item) use ($tag, $array, $sendLogConsoleService) {
                $sendLogConsoleService->execute(request(), 'response', 'access', $tag, $array);
            });
        }
    }

    public static function client_syndicates($play = 0, $result = 0) {
        return self::client_products(2, $play, $result);
    }

    public static function client_scratch_cards($play = 0, $result = 0) {
        return self::client_products(7, $play, $result);
    }

    public static function client_live_lotteries($play = 0, $result = 0) {
        return self::client_products(10, $play, $result);
    }

    public static function client_casino_games($play = 0, $result = 0) {
        return self::client_products(8, $play, $result, true);
    }

    public static function client_sport_books_games($play = 0, $result = 0) {
        return self::client_products(11, $play, $result, true);
    }

    public static function client_raffles($play = 0, $result = 0) {
        return self::client_products(4, $play, $result);
    }

    public static function client_raffles_syndicates($play = 0, $result = 0) {
        return self::client_products(3, $play, $result);
    }

    public static function client_deals($play = 0, $result = 0) {
        return self::client_products(11, $play, $result);
    }

    public static function client_memberships($play = 0, $result = 0) {
        return self::client_products(6, $play, $result);
    }

    public function encode_array($data) {
        $data = is_array($data) ? $data : $data->toArray();
        foreach ($data as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $data[$key] = $this->encode_array($value);
            } elseif (is_string($value)) {
                $value = utf8_encode($value);
                $data[$key] = html_entity_decode($value);
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @return array
     */

    public function record_log2($type, $tag, $array = []) {
        $log = LogConfig::query()
            ->where('type', '=', $type)
            ->where('active', '=', 1)
            ->where(
                function ($query) {
                    $query->whereNull('user_ip')
                        ->orWhere('user_ip', '=', request('user_ip'));
                }
            )
            ->getFromCache();
        if ($log->isNotEmpty()) {
            $sendLogConsoleService = new SendLogConsoleService();
            $log->each(function ($item) use ($tag, $array, $sendLogConsoleService) {
                $sendLogConsoleService->execute(request(), 'request-response', 'access', $tag, $array);
            });
        }
    }

    /**
     * @param $message
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code) {
        if (is_string($message)) {
            $message = ['message' => [$message]];
        }
        return response()->json(['error' => $message, 'code' => $code], $code);
    }


    public function errorCatchResponse(
        $exception,
        $message = '',
        $code = Response::HTTP_INTERNAL_SERVER_ERROR
    ) {
        $infoException = GetInfoFromExceptionService::execute(request(), $exception);
        SendLogUserRequestResponseService::execute($infoException);
        if (config('app.debug') === true) {
            return $this->errorResponseWithMessage($infoException, $message, $code);
        }

        return $this->errorResponseWithMessage([], $message, $code);

    }

    public function getClientExceptionMessageError($exception)
    {
        if ($exception instanceof ServerException) {
            return $exception->getResponse()
                ->getBody()
                ->getContents();
        }
        return null;
    }

    public function getErrorMessageClientExternal($messageException)
    {
        if($messageException === null){
            return null;
        }

        $messageException = json_decode($messageException);
        $messageException = property_exists($messageException, 'data') ? $messageException->data : '';
        $messageException = property_exists($messageException, 'message_error') ? $messageException->message_error : '';
        return $messageException;
    }

    public function validateTraceDescriptionError($exception)
    {
        try {
            return json_encode($exception->getTrace());
        }
        catch(Exception $exception) {
            return [];
        }
    }

    public function errorResponseWithMessage(
        $data = [],
        $message = '',
        $code = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return response()->json([ 'code' => $code, 'message' => $message, 'data' => $data ], $code);

    }

    /**
     * @param Model $instance
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showOne(Model $instance, $code = 200) {
        SendLogUserRequestResponseService::execute($instance);
        $transformer = $instance->transformer;
        $instance = $this->transformData($instance, $transformer);
        return $this->successResponse($instance, $code);
    }

    /**
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data, $code = 200) {
        $data['code'] = $code;
        $data = $this->encode_array($data);
        $headers = [ 'content-type' => 'application/json', 'cache-control' => 'no-cache' ];
        SendLogUserRequestResponseService::execute($data);
        return response()->json($data, $code, $headers);
    }

    public function successResponseWithMessage($data = [], $message = "", $code = 200, $encodeArray = false)
    {
        $response[ 'code' ]    = $code;
        $response[ 'message' ] = $message;
        if ($encodeArray === true) {
            $data = $this->encode_array($data);
        }
        $response[ 'data' ]    = $data;

        $headers = [
            'content-type'  => 'application/json',
            'cache-control' => 'no-cache',
        ];
        SendLogUserRequestResponseService::execute($data);
        return response()->json($response, $code,$headers );
    }

    /**
     * @param Collection $collection
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showAll(Collection $collection, $code = 200) {
        SendLogUserRequestResponseService::execute($collection);
        if ($collection->isEmpty()) {
            return $this->successResponse(array('data' => []), $code);
        }
        if (is_array($collection->first())) {
            $transformer = null;
        } else {
            $transformer = $collection->first()->transformer;
        }
        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);
        return $this->successResponse($collection, $code);
    }

    /**
     * @param Collection $collection
     * @param null       $transformer
     * @return Collection
     */
    protected function filterData(Collection $collection, $transformer = null) {

        $request_query = [
            "user_ip",
            "oauth_client_id",
            "client_site_id",
            "client_sys_id",
            "client_lang",
            "user_id",
            "client_country_id",
            "country_currency",
            "client_country_iso",
            "client_domain",
            "client_partner",
            "per_page",
            "page",
            "sort_by_asc",
            "sort_by_desc",
            "filter",
            "pixels",
        ];
        $parameters = collect(request()->query());
        $parameters = $parameters->filter(function ($item, $key) use ($request_query) {
            return !in_array($key, $request_query);
        });
        foreach ($parameters as $query => $value) {
            $attribute = null;
            if (is_null($transformer)) {
                $attribute = $query;
            } else {
                $attribute = $transformer::originalAttribute($query);
            }
            if (isset($attribute) && isset($value)) {
                if (isset($collection->first()[$attribute]) && $collection->first()[$attribute] instanceof Collection) {
                    $collection = $collection->filter(function ($val, $key) use ($attribute, $value) {
                        return $val[$attribute]->has($value);
                    });
                } else {
                    $collection = $collection->where($attribute, $value);
                }
            }
        }
        return $collection;
    }

    /**
     * @param Collection $collection
     * @param null $transformer
     * @return Collection|mixed
     */
    protected function sortData(Collection $collection, $transformer = null) {
        if (request()->has('sort_by_desc')) {
            $attribute = is_null($transformer) ? request()->sort_by_desc : $transformer::originalAttribute(request()->sort_by_desc);
            $collection = $collection->sortByDesc->{$attribute};
        }
        if (request()->has('sort_by_asc')) {
            $attribute = is_null($transformer) ? request()->sort_by_asc : $transformer::originalAttribute(request()->sort_by_asc);
            $collection = $collection->sortBy->{$attribute};
        }
        return $collection;
    }

    /**
     * @param Collection $collection
     * @param null       $transformerExternal
     * @return array|Collection
     */
    public function setTransformToCollection(Collection $collection)
    {
        if ($collection->isEmpty()) {
            return ['data'=> collect([])];
        }
        if (is_array($collection->first())) {
            $transformer = null;
        } else {
            $transformer = $collection->first()->transformer;
        }
        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->transformData($collection, $transformer);

        return $collection;
    }

    public function getInternalDataFromTransform($collection)
    {
        return array_key_exists('data', $collection) ? $collection['data'] : $collection;
    }

    /**
     * @param Collection $collection
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showAllNoPaginated(Collection $collection, $code = 200) {
        $t1 = round(microtime(true) * 1000);
        SendLogUserRequestResponseService::execute($collection);
        if ($collection->isEmpty()) {
            return $this->successResponse(array('data' => []), $code);
        }
        $collection = $this->setTransformToCollection($collection);
//        $this->record_log2('time', 'Transform List --- ' . (round((microtime(true) * 1000) - $t1, 2)));
        return $this->successResponse($collection, $code);
    }

    /**
     * @param $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function showMessage($message, $code = 200) {
        return $this->successResponse(['data' => $message], $code);
    }

    /**
     * @param Collection $collection
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    protected function paginate(Collection $collection, $perPage = 15) {
        SendLogUserRequestResponseService::execute($collection);
        $rules = [
            'per_page' => 'integer|min:3|max:50'
        ];
        Validator::validate(request()->all(), $rules);
        $page = LengthAwarePaginator::resolveCurrentPage();

        if (request()->has('per_page')) {
            $perPage = (int)request()->per_page;
        }
        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());
        return $paginated;
    }

    /**
     * @param $data
     * @param $transformer
     * @return array
     */
    protected function transformData($data, $transformer) {
        if (is_null($transformer)) {
            return ['data' => $data];
        }
        $transformation = fractal($data, new $transformer);
        $transformation = $transformation->toArray();
        $pixel = request('pixel');
        if (isset($pixel)) {
            //FIXME adjust pixel or permanent remove by dont use in newWinTrillions
            // $transformation["data"]["pixels"] = [$pixel];
        }
        return $transformation;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function cacheResponse($data) {
        $exclude_endpoints = [
            '/api/users/wallet',
        ];
        if (request()->server('REQUEST_METHOD') === 'GET' && !in_array(request()->server('REDIRECT_URL'), $exclude_endpoints)) {
            $url = request()->url();
            $queryParams = request()->query();
            ksort($queryParams);
            $queryString = http_build_query($queryParams);
            $fullUrl = "{$url}?{$queryString}";
            return Cache::remember($fullUrl, 5, function () use ($data) {
                return $data;
            });
        } else {
            return $data;
        }

    }

}
