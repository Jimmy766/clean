<?php

namespace App\Core\Rapi\Controllers;

use App\Core\Carts\Models\Cart;
use App\Core\Rapi\Models\Payway;
use App\Core\Base\Traits\CartUtils;
use App\Core\Rapi\Transforms\PaywayTransformer;
use App\Core\Users\Models\User;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaywayController extends ApiController
{
    use CartUtils;
    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials');
        $this->middleware('transform.input:' . PaywayTransformer::class);

    }

    /**
     * @SWG\Post(
     *   path="/payways",
     *   summary="Show payways list",
     *   tags={"Payways"},
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="cart_id",
     *     in="formData",
     *     description="Cart id",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data",
     *         type="array",
     *         @SWG\Items(ref="#/definitions/Payway"),
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index(Request $request) {
        $rules = [
            'crt_id' => 'integer|exists:mysql_external.carts',
        ];
        $this->validate($request, $rules);

        if ($request->crt_id) {
            $validation = $this->validateCart($request->crt_id);
            if ($validation) return $validation;
            $cart = Cart::find($request->crt_id);
            $cart_total = $cart->crt_total;
            if ($cart->crt_currency != 'USD') {
                $cart_total  *= $this->convertCurrency($cart->crt_currency, 'USD');
            }
        }

        $payways = Payway::where(
            function ($query) use ($request) {
                switch ($request['client_sys_id']) {
                    case 1:
                        $query->where('pay_active_tri', '=', 1);
                        break;
                    case 5:
                        $query->where('pay_active_cgl', '=', 1);
                }
            })
            ->where(function ($query) use ($request) {
                $query->where('country_id', '=', 0)
                    ->orWhere('country_id', 'like', '%'.$request['client_country_id'].'%');
            })
            ->where(function ($query) use ($request) {
                $query->where('black_country_list', '=', '')
                    ->orWhere('black_country_list', 'not like', '%'.$request['client_country_id'].'%');
            })
            ->where(function ($query) use ($request) {
                $query->where('currency', '=', '')
                    ->orWhere('currency', 'like', '%'.$request['country_currency'].'%');
            });
        if ($request->crt_id) {
            $payways = $payways->where(function ($query) use ($cart_total) {
                $query->where('min_amount_usd', '<=', $cart_total)
                    ->orWhere('min_amount_usd', '=', NULL);
            });
        }

        if ($request['user_id'] != 0) {
            $user = User::find($request['user_id']);
            if ($user->usr_level == 0) {
                $payways = $payways->whereIn('pay_id', [1,2,4]);
            }
            $payways = $payways->orWhere('user_test', 'like', '%'.$request['user_id'].'%');
        }
        $payways = $payways->orderBy('pay_id')->get();
        return $this->showAllNoPaginated($payways);
    }
}
