<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Rapi\Models\Payment;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Rapi\Models\Payment $payment
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/user_transactions/payments/{payment}",
     *   summary="Show user payment details",
     *   tags={"User Transactions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="payment",
     *     in="path",
     *     description="Payment Id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data",
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Payment"),
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(Payment $payment) {
        $user = Auth::user();
        if ($user->usr_id != $payment->usr_id) {
            return $this->errorResponse(trans('lang.payment_forbidden'), 422);
        }
        if ($payment->status_process != 1) {
            return $this->errorResponse(trans('lang.payment_error'), 422);
        }
        return $this->showOne($payment);
    }
}
