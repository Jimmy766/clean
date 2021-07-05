<?php

namespace App\Core\Countries\Controllers;

use App\Core\Countries\Models\Country;
use App\Core\Users\Services\GetCountriesByUserIpService;
use App\Core\Countries\Services\GetCountriesService;
use App\Http\Controllers\ApiController;
use Swagger\Annotations as SWG;

class CountryController extends ApiController
{
    /**
     * @var GetCountriesService
     */
    private $getCountriesService;
    /**
     * @var \App\Core\Users\Services\GetCountriesByUserIpService
     */
    private $getCountriesByUserIpService;

    public function __construct(
        GetCountriesService $getCountriesService,
        GetCountriesByUserIpService $getCountriesByUserIpAndGetUrlService
    ) {
        parent::__construct();
        $this->middleware('auth:api')->except(
            [ "index", "states" ]
        );
        $this->middleware('client.credentials')->only(
            [ "index", "states" ]
        );
        $this->getCountriesService = $getCountriesService;
        $this->getCountriesByUserIpService = $getCountriesByUserIpAndGetUrlService;
    }

    /**
     * @SWG\Get(
     *   path="/countries",
     *   summary="Show countries list ",
     *   tags={"Location"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Country")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $countries = $this->getCountriesService->execute();
        return $this->showAllNoPaginated($countries);
    }

    /**
     * @SWG\Get(
     *   path="/countries/states/{country}",
     *   summary="Show states by country",
     *   tags={"Location"},
     *   @SWG\Parameter(
     *     name="country",
     *     in="path",
     *     description="Country Id.",
     *     required=true,
     *     type="integer",
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/State")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function states(Country $country)
    {
        $states = $country->states ? $country->states : [];
        return $this->showAllNoPaginated($states);
    }

    /**
     * @SWG\Get(
     *   path="/countries/by-user-ip",
     *   summary="Show countries and iso by user ip",
     *   tags={"Location"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array",
     *                                        @SWG\Items(ref="#/definitions/Country")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function getCountriesByUserIp()
    {
        $userIp = request()->user_ip;

        [ $iso, $country] =
            $this->getCountriesByUserIpService->execute($userIp);

        $data = [
            'user_ip'   => $userIp,
            'iso'       => $iso,
            'countries' => $country,
        ];

        return $this->successResponseWithMessage($data);
    }
}
