<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Rapi\Models\Deposit;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api');
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Rapi\Models\Deposit $deposit
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/user_transactions/deposits/{deposit}",
     *   summary="Show user deposit details",
     *   tags={"User Transactions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="deposit",
     *     in="path",
     *     description="Deposit Id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data",
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Deposit"),
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(Deposit $deposit) {
        if ($deposit->cart_type != 3) {
            return $this->errorResponse(trans('lang.no_deposit'), 422);
        }
        $user = Auth::user();
        if ($user->usr_id != $deposit->usr_id) {
            return $this->errorResponse(trans('lang.deposit_forbidden'), 422);
        }
        return $this->showOne($deposit);
    }

}
