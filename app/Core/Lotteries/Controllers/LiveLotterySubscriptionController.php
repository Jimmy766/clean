<?php

namespace App\Core\Lotteries\Controllers;

use App\Core\Lotteries\Models\LiveLottery;
use App\Core\Lotteries\Models\LiveLotterySubscription;
use App\Core\Lotteries\Transforms\LiveLotterySubscriptionTransformer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class LiveLotterySubscriptionController extends ApiController {

    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('transform.input:' . LiveLotterySubscriptionTransformer::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * @SWG\Get(
     *   path="/live_lottery_subscriptions",
     *   summary="Show user live lotteries subscriptions",
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
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/LiveLotterySubscription")),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function index() {
        $user = request()->user();
        $lot_ids = LiveLottery::where('lot_live', '=', 1)->pluck('lot_id');
        $inactive = LiveLotterySubscription::with(['lottery.modifiers', 'modifier', 'cart_subscription', 'subscription_picks.subscription.lottery', 'draw.lottery', 'ticket.subscription.modifier', 'ticket.subscription.lottery'])
            ->whereIn('lot_id', $lot_ids)
            ->where('usr_id', '=', $user->usr_id)
            ->whereRaw('((sub_tickets - sub_emitted = 0) or (sub_status = 2))')
            ->whereHas('draw',function ($query) {
                $query->whereNotIn('draw_status', [0, 2]);
            })
            ->whereHas('ticket')
            ->whereHas('cart_subscription')
            ->orderBy('sub_buydate', 'desc')
            ->limit(config('constants.inactive_qty'))
            ->get();
        $active = LiveLotterySubscription::with(['lottery.modifiers', 'modifier', 'cart_subscription', 'subscription_picks.subscription.lottery', 'draw.lottery', 'ticket.subscription.modifier', 'ticket.subscription.lottery'])
            ->whereIn('lot_id', $lot_ids)
            ->where('usr_id', '=', $user->usr_id)
            ->whereRaw('((sub_tickets > sub_emitted) and (sub_status != 2))')
            ->whereHas('draw',function ($query) {
                $query->whereIn('draw_status', [0, 2]);
            })
            ->whereHas('ticket')
            ->whereHas('cart_subscription')
            ->orderBy('sub_buydate', 'desc')
            ->limit(config('constants.inactive_qty'))
            ->get();
        $subscription_live_list = $active->concat($inactive)->sortByDesc('sub_buydate');

        return $this->showAllNoPaginated($subscription_live_list);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Core\Lotteries\Models\LiveLotterySubscription $liveLotterySubscription
     *
     * @return \Illuminate\Http\JsonResponse
     */

    /**
     * @SWG\Get(
     *   path="/live_lottery_subscriptions/{live_lottery_subscription}",
     *   summary="Show user live lottery subscriptions details ",
     *   tags={"Subscriptions"},
     *   @SWG\Parameter(
     *     name="live_lottery_subscription",
     *     in="path",
     *     description="Live Lottery Subscription Id.",
     *     required=true,
     *     type="integer"
     *   ),
     *   security={
     *     {"password": {}, "user_ip":{},  "Content-Language":{}},
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *         @SWG\Property(
     *         property="data",
     *         allOf={
     *          @SWG\Schema(ref="#/definitions/LiveLotterySubscription"),
     *         }
     *       ),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=403, ref="#/responses/403"),
     *   @SWG\Response(response=404, ref="#/responses/404"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */
    public function show(LiveLotterySubscription $live_lottery_subscription) {
        if ($live_lottery_subscription->lottery == null || $live_lottery_subscription->lottery->lot_live != 1) {
            return $this->errorResponse(trans('lang.no_live_lottery_subscription'), 422);
        }
        if (request()->user()->usr_id === $live_lottery_subscription->usr_id) {
            return $this->showOne($live_lottery_subscription);
        } else {
            return $this->errorResponse(trans('lang.subscription_forbidden'), 422);
        }
    }
}
