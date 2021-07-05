<?php

    namespace App\Core\Memberships\Controllers;

    use App\Core\Memberships\Models\Membership;
    use App\Core\Memberships\Models\MembershipExclusiveProduct;
    use App\Core\Memberships\Transforms\MembershipExclusiveProductTransformer;
    use App\Http\Controllers\ApiController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class MembershipExclusiveProductController extends ApiController {

        public function __construct() {
            parent::__construct();
            $this->middleware('auth:api');//->only('index','show');
            //$this->middleware('transform.input:' . MembershipTransformer::class);
        }

        /**
         * @SWG\Get(
         *   path="/membership_exclusive_products",
         *   summary="Show memberships exclusive products list ",
         *   tags={"Memberships"},
         *   security={
         *     {"password": {}, "user_ip":{},  "Content-Language":{}}
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/MembershipExclusiveProductSyndicate")),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=422, ref="#/responses/404"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         */
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function index() {
            $membership_level = Membership::whereNotNull('bonus_id')->first()->level;
            if (Auth::user()->usr_membership_level < $membership_level) // Can't load if user don't have at least level 2 membership
                return $this->errorResponse(trans('lang.membership_exclusive_product_forbidden'), 403);
            $membership_exclusive_products = MembershipExclusiveProduct::where('active', '=',1)
                ->with('bonus.products','syndicate')
                ->get();
            return $this->showAllNoPaginated($membership_exclusive_products);
        }

        /**
         * @SWG\Get(
         *   path="/membership_exclusive_products/{membership_exclusive_products}",
         *   summary="Show membership details ",
         *   tags={"Memberships"},
         *   @SWG\Parameter(
         *     name="membership_exclusive_products",
         *     in="path",
         *     description="Membership Exclusive Products Id.",
         *     required=true,
         *     type="integer"
         *   ),
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(
         *         property="data",
         *         allOf={
         *          @SWG\Schema(ref="#/definitions/MembershipExclusiveProductSyndicate"),
         *         }
         *       ),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=404, ref="#/responses/404"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}}
         *   },
         * )
         *
         */
        /**
         * Display the specified resource.
         *
         * @param  \App\Core\Memberships\Models\Membership $membership
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function show(MembershipExclusiveProduct $membershipExclusiveProduct) {
            $membership_level = Membership::whereNotNull('bonus_id')->first()->level;
            if (Auth::user()->usr_membership_level < $membership_level || $membershipExclusiveProduct->active != 1) // Can't load if user don't have at least level 2 membership
                return $this->errorResponse(trans('lang.membership_exclusive_product_forbidden'), 403);
            return $this->showOne($membershipExclusiveProduct);
        }
    }
