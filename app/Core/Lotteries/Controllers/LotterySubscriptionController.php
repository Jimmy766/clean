<?php

namespace App\Core\Lotteries\Controllers;

use App\Core\Base\Services\SetPaginationTransformService;
use App\Core\Lotteries\Models\LogUserActionRenew;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Lotteries\Models\LotterySubscription;
use App\Core\Lotteries\Transforms\LotterySubscriptionTransformer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LotterySubscriptionController extends ApiController
{
    /**
     * @var SetPaginationTransformService
     */
    private $setPaginationTransformService;

    public function __construct(SetPaginationTransformService $setPaginationTransformService) {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('transform.input:' . LotterySubscriptionTransformer::class)->only('update');
        $this->setPaginationTransformService = $setPaginationTransformService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/lotteries_subscriptions",
     *   summary="Show user lotteries subscriptions",
     *   tags={"Subscriptions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="status",
     *     in="query",
     *     description="Subscription status (active, expired)",
     *     required=false,
     *     type="string"
     *   ),
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotterySubscription")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index() {
        $lot_ids = Lottery::where('lot_live', '!=', 1)->pluck('lot_id');
        $lotteries_subscriptions_inactive= LotterySubscription::with(['cart_subscription.cart', 'cart_subscription.price', 'tickets', 'lottery_wheel', 'subscription_picks.lottery_subscription', 'subscription_picks.lottery_subscription.lottery'])
            ->where('usr_id', '=', request()->user()->usr_id)
            ->whereRaw('(((sub_tickets + sub_ticket_extra - sub_emitted) <= 0) or (sub_status = 2))')
            ->whereIn('lot_id', $lot_ids)
            ->orderBy('sub_buydate', 'desc')
            ->limit(config('constants.inactive_qty'))
            ->get();
        $lotteries_subscriptions_active= LotterySubscription::with(['cart_subscription.cart', 'cart_subscription.price', 'tickets', 'lottery_wheel', 'subscription_picks.lottery_subscription', 'subscription_picks.lottery_subscription.lottery'])
            ->where('usr_id', '=', request()->user()->usr_id)
            ->where('sub_status', '!=', 2)
            ->whereRaw('((sub_tickets + sub_ticket_extra - sub_emitted) > 0)')
            ->whereIn('lot_id', $lot_ids)
            ->orderBy('sub_buydate', 'desc')
            ->get();

        $lotteries_subscriptions = $lotteries_subscriptions_active->concat($lotteries_subscriptions_inactive)->sortByDesc('sub_buydate');
        return $this->showAllNoPaginated($lotteries_subscriptions);
    }


    /**
     * @SWG\Get(
     *   path="/lotteries_subscriptions/list",
     *   summary="Show user lotteries subscriptions",
     *   tags={"Subscriptions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="status",
     *     in="query",
     *     description="Subscription status (active, expired)",
     *     required=false,
     *     type="string"
     *   ),
     *
     *   @SWG\Parameter(
     *     name="page_pagination",
     *     in="query",
     *     description="page pagination",
     *     type="integer",
     *     default=1
     *   ),
     *   @SWG\Parameter(
     *     name="size_pagination",
     *     in="query",
     *     description="size pagination",
     *     type="integer",
     *     default=15
     *   ),
     *
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotterySubscription")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request  $request): JsonResponse
    {
        $relations               = [
            'cart_subscription.cart',
            'cart_subscription.price',
            'tickets',
            'lottery_wheel',
            'subscription_picks.lottery_subscription',
            'subscription_picks.lottery_subscription.lottery',
        ];
        $lot_ids = Lottery::query()
            ->where('lot_live', '!=', 1)
            ->getFromCache()
            ->pluck('lot_id');
        $status = $request->status;
        $lotteriesSubscriptions = LotterySubscription::with($relations)
            ->where('usr_id', '=', request()->user()->usr_id)
            ->whereIn('lot_id', $lot_ids)
            ->orderBy('sub_buydate', 'desc');
        if ($status === 'expired') {
            $lotteriesSubscriptions = $lotteriesSubscriptions->whereRaw(
                '(((sub_tickets + sub_ticket_extra - sub_emitted) <= 0) or (sub_status = 2))'
            );
        }
        if ($status !== 'expired') {
            $lotteriesSubscriptions = $lotteriesSubscriptions->where('sub_status', '!=', 2)
                ->whereRaw('((sub_tickets + sub_ticket_extra - sub_emitted) > 0)');
        }
        $lotteriesSubscriptions = $lotteriesSubscriptions->paginateByRequest();

        $lotteriesSubscriptions = $this->setPaginationTransformService->execute($lotteriesSubscriptions);
        $data['lotteries_subscriptions']= $lotteriesSubscriptions;

        return $this->successResponseWithMessage($data, "", Response::HTTP_OK, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Lotteries\Models\LotterySubscription $lottery_subscription
     * @return \Illuminate\Http\Response
     */
    /**
     * @SWG\Get(
     *   path="/lotteries_subscriptions/{lotteries_subscription}",
     *   summary="Show user lottery subscription detail",
     *   tags={"Subscriptions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="lotteries_subscription",
     *     in="path",
     *     description="Lottery Subscription Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(@SWG\Property(
     *       property="data",
     *       allOf={
     *          @SWG\Schema(ref="#/definitions/LotterySubscription"),
     *       }
     *     ),),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(LotterySubscription $lotteries_subscription) {
        if ($lotteries_subscription->lottery == null || $lotteries_subscription->lottery->lot_live == 1) {
            return $this->errorResponse(trans('lang.no_lottery_subscription'), 422);
        }
        if (request()->user()->usr_id === $lotteries_subscription->usr_id) {
            return $this->showOne($lotteries_subscription);
        } else {
            return $this->errorResponse(trans('lang.subscription_forbidden'), 422);
        }
    }

    /**
     * @param \App\Core\Lotteries\Models\LotterySubscription $lotteries_subscription
     * @return \Illuminate\Support\Collection
     */
    /**
     * @SWG\Get(
     *   path="/lotteries_subscriptions/extra_details/{lotteries_subscription}",
     *   summary="Show user lottery subscription extra details",
     *   tags={"Subscriptions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="lotteries_subscription",
     *     in="path",
     *     description="Lottery Subscription Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotterySubscriptionExtraDetail")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function extra_details(LotterySubscription $lotteries_subscription) {
        if ($lotteries_subscription->lottery->lot_live == 1) {
            return $this->errorResponse(trans('lang.no_lottery_subscription'), 422);
        }
        if (request()->user()->usr_id === $lotteries_subscription->usr_id) {
            return $this->successResponse(['data' => $lotteries_subscription->extra_details], 200);
        } else {
            return $this->errorResponse(trans('lang.subscription_forbidden'), 422);
        }
    }

    /**
     * @SWG\Put(
     *   path="/lotteries_subscriptions/{lotteries_subscription}",
     *   summary="Change lottery subscription play mode",
     *   consumes={"application/x-www-form-urlencoded"},
     *   tags={"Subscriptions"},
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Parameter(
     *     name="lotteries_subscription",
     *     in="path",
     *     description="Lottery Subscription Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="renewable",
     *     in="formData",
     *     description="Is lottery renewable",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LotterySubscription")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=405, ref="#/responses/405"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function update(Request $request, LotterySubscription $lotteries_subscription) {
        if ($lotteries_subscription->usr_id != $request->user_id) {
            return $this->errorResponse(trans('lang.subscription_forbidden'), 422);
        }
        $rules = [
            'sub_renew' => 'required|integer|min:0|max:1'
        ];
        $this->validate($request, $rules);
        if ($lotteries_subscription->isActive()) {
            if ($request->sub_renew != $lotteries_subscription->sub_renew) {
                LogUserActionRenew::create([
                    'usr_id' => $request->user_id,
                    'product_type' => 1,
                    'sub_id' => $lotteries_subscription->sub_id,
                    'sub_renew_before' => $lotteries_subscription->sub_renew,
                    'sub_renew_after' => $request->sub_renew,
                    'ip' => $request->user_ip
                ]);
                $lotteries_subscription->sub_renew = $request->sub_renew;
                $lotteries_subscription->on_hold = 0;
                $lotteries_subscription->save();
            }
        } else {
            return $this->errorResponse(trans('lang.inactive_lottery_subscription'), 422);
        }
        return $this->showOne($lotteries_subscription);
    }
}
