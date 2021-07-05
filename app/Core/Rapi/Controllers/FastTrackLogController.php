<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Carts\Models\Cart;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Base\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Swagger\Annotations as SWG;

/**
 * Class FastTrackLogController
 * @package App\Http\Controllers
 */
class FastTrackLogController extends Controller
{
    use ApiResponser;

    public function __construct()
    {
        $this->middleware('check.external_access');
    }

    /**
     * @SWG\Post(
     *   path="/fast-track/cart-confirmed/{cart}",
     *   summary="Show specific asset ",
     *   tags={"FastTrack"},
     *   @SWG\Parameter(
     *     name="cart",
     *     in="path",
     *     description="cart",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"Key-access": {}, "client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(response=200, ref="#/responses/200"),
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
     */
    public function sendLogFastTrackCartConfirmed(Cart $cart)
    {
        $data = [];
       return $this->successResponseWithMessage($data, '');
    }
}
