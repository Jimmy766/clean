<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Rapi\Models\Order;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api');
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Rapi\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/user_transactions/orders/{order}",
     *   summary="Show user order details",
     *   tags={"User Transactions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="order",
     *     in="path",
     *     description="Order Id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data",
     *         type="object",
     *         allOf={
     *           @SWG\Schema(ref="#/definitions/Order"),
     *         },
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(Order $order) {
        $user = Auth::user();
        if ($user->usr_id != $order->usr_id) {
            return $this->errorResponse(trans('lang.order_forbidden'), 422);
        }
        return $this->showOne($order);
    }
}
