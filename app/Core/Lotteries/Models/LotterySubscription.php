<?php

namespace App\Core\Lotteries\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Carts\Models\CartSubscription;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotExceedLimitService;
use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotModifierService;
use App\Core\Countries\Services\GetCountryByCodeCountryService;
use App\Core\Lotteries\Services\GetLotteriesAndCheckInsureBlackListService;
use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Base\Traits\LogCache;
use App\Core\Rapi\Models\Draw;
use App\Core\Lotteries\Models\LotterySubscriptionPick;
use App\Core\Rapi\Models\SubscriptionsPicksWheelsByDraw;
use App\Core\Rapi\Models\Ticket;
use App\Core\Lotteries\Transforms\LotterySubscriptionTransformer;
use App\Core\Rapi\Models\Wheel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;


/**
 * @SWG\Definition(
 *     definition="LotterySubscriptionExtraDetail",
 *     @SWG\Property(
 *       property="draw",
 *       description="Lottery Subscription identifier",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Draw"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="numbers",
 *       description="Numbers selected",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/SubscriptionPicksWheelsByDraw"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       description="Prize",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="prize_value",
 *           description="Prize Value",
 *           type="string",
 *           example="USD : 10"
 *         ),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       description="Tickets",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/Ticket")
 *     ),
 *  ),
 */

class LotterySubscription extends CoreModel
{
    use LogCache;
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'sub_id';
    protected $table = 'subscriptions';
    protected $tickets_count = null;
    public $timestamps = false;
    public $transformer = LotterySubscriptionTransformer::class;

    protected $fillable = [
        'usr_id',
        'lot_id',
        'sub_tickets',
        'sub_tickets_extra',
        'sub_price',
        'sub_buydate',
        'sub_status',
        'sub_emitted',
        'sub_lastdraw_id',
        'sub_Extension',
        'sub_ticket_nextDraw',
        'cts_id',
        'sub_parent',
        'sub_root',
        'sub_renew',
        'marked_for_renewal',
        'pck_type',
        'sys_id',
        'msn_ff',
        'msn_fran',
        'msn_email',
        'msn_acumulado',
        'site_id',
        'sub_subtype',
        'sub_lastDraw_email',
        'sub_ticket_byDraw',
        'sus_id',
        'onhold',
        'sub_type_selector',
        'sub_cant_selector',
        'sub_winning_behaviour',
        'lot_id_big',
        'sub_notes',
        'sub_printable_name',
        'sub_draws_by_ticket',
        'sub_day_to_play',
        'wheel_id',
        'sub_next_draw_id',
        'sub_multiplier',
        'modifier_1',
        'modifier_2',
        'modifier_3',
        'boosted_modifier_id',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'sub_id',
        'usr_id',
        'lot_id',
        'sub_tickets',
        'sub_tickets_extra',
        'sub_price',
        'sub_buydate',
        'sub_status',
        'sub_emitted',
        'sub_lastdraw_id',
        'sub_Extension',
        'sub_ticket_nextDraw',
        'cts_id',
        'sub_parent',
        'sub_root',
        'sub_renew',
        'marked_for_renewal',
        'pck_type',
        'sys_id',
        'msn_ff',
        'msn_fran',
        'msn_email',
        'msn_acumulado',
        'site_id',
        'sub_subtype',
        'sub_lastDraw_email',
        'sub_ticket_byDraw',
        'sus_id',
        'onhold',
        'sub_type_selector',
        'sub_cant_selector',
        'sub_winning_behaviour',
        'lot_id_big',
        'sub_notes',
        'sub_printable_name',
        'sub_draws_by_ticket',
        'sub_day_to_play',
        'wheel_id',
        'sub_next_draw_id',
        'sub_multiplier',
        'modifier_1',
        'modifier_2',
        'modifier_3',
    ];

    public function getPickTypeAttribute() {
        if ($this->pck_type != 3) {
            return trans('lang.quick_pick');
        } else {
            return trans('lang.user_pick');
        }
    }

    public function getLotteryNameAttribute() {
        $lottery = $this->rememberCache('lottery_'.$this->lot_id, Config::get('constants.cache_daily'), function() {
            return $this->lottery;
        });
        return $lottery ? $lottery->name : null;
    }

    public function getCartSuscriptionAttribute() {
        return SetTransformToModelOrCollectionService::execute($this->cart_subscription);
    }

    public function getInsureLotteryJackpotAttribute() {
        /** @var Lottery $lottery */
        $lottery =  $this->lottery;

        if($lottery !== null){
            $lotteries = collect([]);
            $lotteries->push($lottery);
            $idUser = \Auth::id();
            $idsLotteries = $lotteries->pluck('lot_id')->toArray();
            $relations = $lottery->getQueueableRelations();
            $getCountryCode = new GetCountryByCodeCountryService();
            $lotteryInsureBlackListService = new GetLotteriesAndCheckInsureBlackListService($getCountryCode);
            $lotteries = $lotteryInsureBlackListService->execute(
                $idsLotteries, null, $relations, $idUser
            );
            $lottery = $lotteries->first();
            if ($lottery === null){
                return false;
            }
            return $lottery->insure_boosted_jackpot;
        }
    }

    public function getLotteryBoostedJackpotAttribute() {
        $lottery = $this->lottery;
        if($lottery !== null){
            $lotteries = collect([]);
            $lotteries->push($lottery);
            $idUser = \Auth::id();
            $idsLotteries = $lotteries->pluck('lot_id')->toArray();
            $relations = [ 'draw_active.lottery', 'lotteriesBoostedJackpot.lotteriesModifier' ];
            $lotteries = Lottery::query()
                ->whereIn('lot_id', $idsLotteries)
                ->with($relations)
                ->get();
            $lottery = $lotteries->first();
        }
        if($lottery !== null){
            $lotteriesBoostedJackpot = $lottery ? $lottery->lotteriesBoostedJackpot : null;
            $filterModifierJackpot = new FilterBoostedJackpotModifierService();
            $lotteriesBoostedJackpot = $filterModifierJackpot->execute($lotteriesBoostedJackpot);
            return SetTransformToModelOrCollectionService::execute($lotteriesBoostedJackpot);
        }
        return null;
    }

    public function getLotteryRegionAttribute() {
        $lottery_region = $this->rememberCache('lottery_region_'.$this->lot_id, Config::get('constants.cache_daily'), function() {
            $lottery = $this->rememberCache('lottery_'.$this->lot_id, Config::get('constants.cache_daily'), function() {
                return $this->lottery;
            });
            return $lottery ? $lottery->region_attributes : null;
        });
        return $lottery_region;
    }

    public function isActive() {
        return ($this->sub_tickets + $this->sub_ticket_extra - $this->sub_emitted) > 0 && $this->sub_status != 2;
    }

    public function getStatusAttribute() {
        return $this->isActive() ? trans('lang.active_subscription') : trans('lang.expired_subscription');
    }

    public function getStatusTagAttribute() {
        return $this->isActive() ? trans('lang.active_subscription_tag') : trans('lang.expired_subscription_tag');
    }

    public function cart_subscription() {
        return $this->belongsTo(CartSubscription::class, 'cts_id', 'cts_id');
    }

    public function subscription_picks() {
        return $this->hasMany(LotterySubscriptionPick::class, 'sub_id', 'sub_id');
    }

    public function lottery() {
        return $this->belongsTo(Lottery::class, 'lot_id', 'lot_id');
    }

    public function lottery_wheel() {
        return $this->hasOne(Wheel::class, 'wheel_id', 'wheel_id');
    }

    public function getWheelAttribute() {
        return $this->lottery_wheel ? $this->lottery_wheel->transformer ?
            $this->lottery_wheel->transformer::transform($this->lottery_wheel) : $this->lottery_wheel : null;
    }

    public function tickets() {
        return $this->hasMany(Ticket::class, 'sub_id', 'sub_id')->orderByDesc('tck_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets_winnings(){
        return $this->tickets()->whereIn('tck_status',[3,4,6])->where('tck_prize_usr','>',0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets_winnings_pending(){
        return $this->tickets()->whereIn('tck_status',[1,5]);
    }

    public function getPicksAttribute() {
        return $this->rememberCache('lottery_subscription_picks_'.$this->sub_id, Config::get('constants.cache_hourly'), function() {
            $picks = collect([]);
            if ($this->pck_type == 3) {
                $this->subscription_picks()->with(['lottery_subscription', 'lottery_subscription.lottery'])->get()->each(function($item) use ($picks){
                    $pick = $item->transformer ? $item->transformer::transform($item) : $item;
                    $picks->push($pick);
                });
            }
            return $picks;
        });
    }

    public function getSubscriptionsAttribute() {
        return $this->wheel_id > 0 ?
            $this->cart_subscription ? $this->cart_subscription->cts_ticket_byDraw : null
            : $this->sub_ticket_byDraw;
    }

    public function getSubDrawsAttribute() {
        $idUserRequest = request()->user()->usr_id;
        $statusRequest = request()->status;
        $idCart = $this->cart_subscription !== null ? $this->cart_subscription->crt_id : 0;
        $key = "lottery_sub_draws_{$this->lot_id}_{$this->usr_id}_{$this->cts_id} {$idCart}_{$idUserRequest}_{$statusRequest}";

        return $this->rememberCache($key, Config::get('constants.cache_5'), function() {
            $emitted = 0;
            $total = 0;
            if ($this->sub_lastdraw_id == 0) {
                $total += $this->sub_ticket_byDraw != 0 ? ceil(($this->sub_tickets + $this->sub_ticket_extra) / $this->sub_ticket_byDraw) : 0;
            } else {
                $this->tickets_count = $this->tickets_count ? $this->tickets_count :
                    Ticket::where('sub_id', '=', $this->sub_id)->distinct('draw_id')->count('draw_id');
                $total += $this->sub_ticket_byDraw != 0 ? $this->tickets_count +
                    ceil(($this->sub_tickets + $this->sub_ticket_extra - $this->sub_emitted) / $this->sub_ticket_byDraw) : 0;
                if($this->sub_lastdraw_id > 0 && $this->draw->draw_status == 0) {
                    $emitted = $this->tickets_count - 1;
                }else{
                    $emitted = $this->tickets_count;
                }
            }
            return ['emitted' => $emitted, 'total' => $total];
        });
    }

    public function getPrizeAttribute() {

        $prize_arr = [];
        if ($this->tickets->isEmpty()) {
            //return [request()->user()->curr_code => 0];
            $prize_arr[0]["key"] = request()->user()->curr_code;
            $prize_arr[0]["value"] = 0;
            return $prize_arr;
        }
        $prize = [];
        $this->tickets->each(function ($item) use (&$prize) {
            if (!isset($prize[$item->curr_code])) {
                $prize[$item->curr_code] = 0;
            }
            $prize[$item->curr_code] += $item->tck_prize_usr;
        });

        $prize_arr = [];
        $i = 0;
        foreach($prize as $key => $value) {
          $prize_arr[$i]["key"] = $key;
          $prize_arr[$i]["value"] = $value;
          $i++;
        }

        return $prize_arr;
    }

    public function getPrizePendingAttribute() {

        $prize_arr = [];
        if ($this->tickets->isEmpty()) {
            //return [request()->user()->curr_code => 0];
            $prize_arr[0]["key"] = request()->user()->curr_code;
            $prize_arr[0]["value"] = 0;
            return $prize_arr;
        }
        $prize_pending = [];
       $this->tickets->each(function ($item) use (&$prize_pending) {
            if ($item->tck_status == 1 || $item->tck_status == 5) {
                if (!isset($prize_pending[$item->curr_code])) {
                    $prize_pending[$item->curr_code] = 0;
                }
                $prize_pending[$item->curr_code] += $item->tck_prize_usr;
            }
        });

        $prize_arr = [];
        $i = 0;
        foreach($prize_pending as $key => $value) {
          $prize_arr[$i]["key"] = $key;
          $prize_arr[$i]["value"] = $value;
          $i++;
        }

        if ($prize_arr == []) {
          $prize_arr[0]["key"] = request()->user()->curr_code;
          $prize_arr[0]["value"] = 0;
        }

        return $prize_arr;
    }

    public function picks_wheel_by_draw() {
        return $this->hasMany(SubscriptionsPicksWheelsByDraw::class, 'sub_id', 'sub_id');
    }

    public function is_wheel() {
        return $this->wheel_id > 0;
    }

    public function draw() {
        return $this->hasOne(Draw::class, 'draw_id', 'sub_lastdraw_id');
    }

    public function getPlayModeAttribute() {
        $cart_subscription = $this->cart_subscription ? $this->cart_subscription : null;
        $price = $cart_subscription && $cart_subscription->cts_prc_id > 0 ? $cart_subscription->price : null;
        $prc_model_type = $price ? $price->prc_model_type : null;
        $price_time = $price ? $price->prc_time : null;
        if ($this->sub_renew == 1) {   // no es renovable
            return '#PURCHASE_MODEL_INDIVIDUAL_DRAWS#';
        } else {  // es renovable
            if ($prc_model_type == 2) {
                return '#PURCHASE_MODEL_MONTH_SUBS#';
            } elseif ($prc_model_type == 3) {
                return $price_time.' #MONTHS#';
            } else {
                return '#OLDMODEL#';
            }
        }
    }

    public function getDescriptorAttribute() {
        return $this->cart_subscription ? ($this->cart_subscription->cart ? $this->cart_subscription->cart->crt_descriptor : '') : null;
    }

    public function getModelTypeAttribute() {
        $cart_subscription = $this->cart_subscription ? $this->cart_subscription : null;
        $price = ($cart_subscription && $cart_subscription->cts_prc_id > 0) ? $cart_subscription->price : null;
        return $price ? $price->prc_model_type : null;
    }

    public function getOrderAttribute() {
        return $this->cart_subscription ? $this->cart_subscription->crt_id : null;
    }

    public function getExtraDetailsAttribute() {
        $result = collect([]);
        $this->tickets->load("subscription.lottery", "draw.lottery")->groupBy('draw_id')
            ->each(function($item) use ($result) {
            $draw = $item->first()->draw ?
                $item->first()->draw->transformer ?
                    $item->first()->draw->transformer::transform($item->first()->draw) : $item->first()->draw : null;
            $numbers = null;
            $prize = null;
            $lines = collect([]);

            if($draw && !is_array($draw["results"]) && $draw["results"] == -1){
                $draw["results"] = [];
            }

            $item->each(function(Ticket $item) use ($lines) {
                $lines->push($item->transformer ? $item->transformer::transform($item) : $item);
            });
            if ($item->first()->subscription->is_wheel()) {
                $numbers = collect([]);
                $item->first()->subscription->picks_wheel_by_draw->where('draw_id', '=', $item->first()->draw->draw_id)
                    ->each(function ($item, $key) use ($numbers) {
                        $number = $item->transformer ? $item->transformer::transform($item) : $item;
                        $numbers->push($number);
                    });
                $prize = ['curr_code' => $item->first()->curr_code, 'prize' => $item->first()->tck_prize_usr];
                $result->push(['draw'=>$draw, 'numbers' => $numbers, 'prize' => $prize, 'tickets' => $lines]);
            } else {
                $result->push(['draw'=>$draw, 'tickets' => $lines]);
            }
        });
        return $result;
    }

    public function getLotteryDrawDateAttribute() {
        return $this->rememberCache('lottery_draw_date_'.$this->lot_id, Config::get('constants.cache_5'), function() {
            return $this->lottery ? $this->lottery->draw_date : null;
        });
    }
}
