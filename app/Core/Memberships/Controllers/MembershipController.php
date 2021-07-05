<?php

    namespace App\Core\Memberships\Controllers;

    use App\Core\Clients\Models\Client;
    use App\Core\Clients\Models\ClientProduct;
    use App\Core\Memberships\Models\Membership;
    use App\Core\Rapi\Models\Site;
    use App\Core\Memberships\Transforms\MembershipTransformer;
    use App\Http\Controllers\ApiController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class MembershipController extends ApiController {

        public function __construct() {
            parent::__construct();
            $this->middleware('client.credentials')->only('index','show');
            $this->middleware('transform.input:' . MembershipTransformer::class);
        }

        /**
         * @SWG\Get(
         *   path="/memberships",
         *   summary="Show memberships list ",
         *   tags={"Memberships"},
         *   security={
         *     {"client_credentials": {}, "user_ip":{},  "Content-Language":{}},
         *     {"password": {}, "user_ip":{},  "Content-Language":{}}
         *   },
         *   @SWG\Response(
         *     response=200,
         *     description="Successful operation",
         *     @SWG\Schema(
         *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/Membership")),
         *     ),
         *   ),
         *   @SWG\Response(response=401, ref="#/responses/401"),
         *   @SWG\Response(response=403, ref="#/responses/403"),
         *   @SWG\Response(response=500, ref="#/responses/500"),
         * )
         *
         */
        /**
         * Display a listing of the resource.
         *
         * @return \Illuminate\Http\JsonResponse
         */
        public function index(Request $request) {
            $memberships = Membership::where('active', '=',1)->where('sys_id', '=', request('client_sys_id'))
            ->whereIn('id', self::client_memberships(1)->pluck('product_id'))->get();
            return $this->showAllNoPaginated($memberships);
        }

        /**
         * @SWG\Get(
         *   path="/memberships/{membership}",
         *   summary="Show membership details ",
         *   tags={"Memberships"},
         *   @SWG\Parameter(
         *     name="membership",
         *     in="path",
         *     description="Membership Id.",
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
         *          @SWG\Schema(ref="#/definitions/Membership"),
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
        public function show(Membership $membership) {
            if ($membership->sys_id != request('client_sys_id') ) {
                return $this->errorResponse(trans('lang.invalid_client'), 422);
            }

            if (!$membership->active || !self::client_memberships(1)->pluck('product_id')->contains($membership->id)) {
                return $this->errorResponse(trans('lang.membership_forbidden'), 422);
            }
            return $this->showOne($membership);
        }
    }
