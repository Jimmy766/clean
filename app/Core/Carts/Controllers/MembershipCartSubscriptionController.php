<?php

namespace App\Core\Carts\Controllers;

use App\Core\Memberships\Models\Membership;
use App\Core\Memberships\Models\MembershipCartSubscription;
use App\Core\Base\Traits\CartUtils;
use App\Core\Memberships\Transforms\MembershipCartSubscriptionTransformer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MembershipCartSubscriptionController extends ApiController
{
    use CartUtils;
    /**
     * CartSubscriptionController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->middleware('client.credentials');
        $this->middleware('auth:api');
        $this->middleware('transform.input:' . MembershipCartSubscriptionTransformer::class)->only(['store']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Post(
     *   path="/cart_memberships",
     *   summary="Create Cart Memberships",
     *   tags={"Cart Memberships"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="cart_id",
     *     in="formData",
     *     description="Cart Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="memberships_id",
     *     in="formData",
     *     description="Membership Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=201,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function store(Request $request) {

        $rules = [
            'crt_id' => 'required|integer|exists:mysql_external.carts',
            'memberships_id' => 'required|integer|' . Rule::exists('mysql_external.memberships','id')
                    ->where('active', 1)
                    ->where('sys_id', request('client_sys_id'))
                    ->whereNotNull('bonus_id')
                    . '|' .
                    Rule::in(self::client_memberships(1)->pluck('product_id')),
        ];

        $this->validate($request, $rules);
        //          validate cart from user and valid in site
        $validation = $this->validateCart($request->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($request->crt_id);
        if ($lock) return $lock;
        $membership = Membership::find($request->memberships_id);
        $user = Auth::user();
        if ($user) {
            if ($membership->level <= $user->usr_membership_level)
                return $this->errorResponse(trans('lang.membership_forbidden'), 403);
        }

        // Get Active price with permited country
        $membership_price_line = $membership->prices()->first()->price_line_country_check();

        // Delete any membership from cart if exists
        $membership_cart_subscription = MembershipCartSubscription::where('crt_id','=',$request->crt_id)->first();
        if ($membership_cart_subscription){
            $cart = $membership_cart_subscription->cart;
            $cart->crt_total -= $membership_cart_subscription->cts_price;
            $membership_cart_subscription->delete();
        }

        $membership_cart_subscription = new MembershipCartSubscription();
        $membership_cart_subscription->crt_id = $request->crt_id;
        $membership_cart_subscription->memberships_id = $request->memberships_id;
        $membership_cart_subscription->cts_price = $membership_price_line->prcln_price;
        $membership_cart_subscription->sub_id = 0;
        $membership_cart_subscription->cts_renew = $membership->no_renew;
        $membership_cart_subscription->cts_prc_id = $membership_price_line->prc_id;
        $membership_cart_subscription->bonus_id = $membership->bonus_id;
        $membership_cart_subscription->save();

        $cart = $membership_cart_subscription->cart;
        $cart->crt_total += $membership_cart_subscription->cts_price;
        $this->cartAmounts($cart);
        $request->merge(['pixel' => $cart->cart_step1()]);
        return $this->showOne($cart, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Carts\Models\CartSubscription $cart_subscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/cart_memberships/{cart_membership}",
     *   summary="Show Cart Membership details ",
     *   tags={"Cart Memberships"},
     *   @SWG\Parameter(
     *     name="cart_membership",
     *     in="path",
     *     description="Cart Membership Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/CartMembershipSubscription"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(MembershipCartSubscription $membership_cart_subscription) {
        $validation = $this->validateCart($membership_cart_subscription->crt_id);
        if ($validation) return $validation;
        return $this->showOne($membership_cart_subscription);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Core\Carts\Models\CartSubscription $cart_subscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Delete(
     *   path="/cart_memberships/{cart_membership}",
     *   summary="Delete Cart Membership",
     *   tags={"Cart Memberships"},
     *   @SWG\Parameter(
     *     name="cart_membership",
     *     in="path",
     *     description="Cart Membership Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
     *     {"password": {}, "user_ip":{},  "Content-Language":{}}
     *   },
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="data", allOf={ @SWG\Schema(ref="#/definitions/Cart"), }),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=422, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function destroy(MembershipCartSubscription $membership_cart_subscription) {
        $validation = $this->validateCart($membership_cart_subscription->crt_id);
        if ($validation) return $validation;
        $lock = $this->check_for_cart_lock($membership_cart_subscription->crt_id);
        if ($lock) return $lock;
        $cart = $membership_cart_subscription->cart;
        $cart->crt_total -= $membership_cart_subscription->cts_price;
        $membership_cart_subscription->delete();
        $this->cartAmounts($cart);
        return $this->showOne($cart);
    }
}
