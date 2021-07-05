<?php

namespace App\Core\Rapi\Controllers;


use App\Core\Clients\Models\Client;
use App\Core\Rapi\Models\Promotion;
use App\Core\Base\Services\ClientService;
use App\Core\Rapi\Transforms\PromotionTransformer;
use App\Http\Controllers\ApiController;
use App\Transformers\PromotionCodeTransformer;
use Illuminate\Http\Request;


class PromotionController extends ApiController
{

    /**
     * PromotionController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api')->except('index','show','code_info');
        $this->middleware('client.credentials')->only( 'index','show','code_info');
        $this->middleware('transform.input:' . PromotionTransformer::class);
    }

    /**
     *
     * @SWG\Post(
     *   path="/promocode_info/{code}",
     *   summary="Show promocode info ",
     *   tags={"Promocode info"},
     * @SWG\Parameter(
     *     name="code",
     *     in="path",
     *     description="Promo code.",
     *     required=true,
     *     type="string"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/PromotionCode")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

    public function code_info($code)
    {
        $sys_id = ClientService::getSystem(request()['oauth_client_id']);

        $promotion = Promotion::where('code', '=' ,$code)
                    ->where('sys_id','=',$sys_id)
                    ->where('expiration_date','>=',date("Y-m-d"))
                    ->first();
        if($promotion)
        {
            $promotion->transformer = PromotionTransformer::class;
        }

        if(!$promotion)
            return $this->errorResponse(trans('lang.invalid_promocode'), 422);

        return $this->showOne($promotion, 201);
    }




}
