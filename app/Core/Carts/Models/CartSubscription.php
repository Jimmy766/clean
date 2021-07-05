<?php

namespace App\Core\Carts\Models;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Countries\Services\GetCountryByCodeCountryService;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Lotteries\Models\LotteryFirstDayToPlay;
use App\Core\Lotteries\Models\LotterySubscription;
use App\Core\Lotteries\Services\Boosted\CalculateMountsBoostedJackpotService;
use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotExceedLimitService;
use App\Core\Lotteries\Services\Boosted\FilterBoostedJackpotModifierService;
use App\Core\Lotteries\Services\GetLotteriesAndCheckInsureBlackListService;
use App\Core\Rapi\Models\Draw;
use App\Core\Rapi\Models\Price;
use App\Core\Carts\Transforms\CartSubscriptionTransformer;
use App\Core\Rapi\Models\Wheel;
use Illuminate\Database\Eloquent\Model;

class CartSubscription extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cts_id';
    public $timestamps = false;
    protected $table = 'cart_suscriptions';
    public $transformer = CartSubscriptionTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'crt_id',
        'lot_id',
        'cts_subExtension',
        'cts_tickets',
        'cts_price',
        'cts_ticket_extra',
        'cts_pck_type',
        'cts_ticket_byDraw',
        'cts_draws',
        'cts_ticket_nextDraw',
        'cts_winning_behaviour',
        'cts_renew',
        'cts_prc_id',
        'cts_printable_name',
        'cts_draws_by_ticket',
        'cts_day_to_play',
        'cts_next_draw_id',
        'bonus_id',
        'cts_modifier_1',
        'cts_modifier_2',
        'cts_modifier_3',
        'boosted_modifier_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->cts_modifier_1 = $model->cts_modifier_1 ?? ModelConst::DISABLED;
            $model->cts_modifier_2 = $model->cts_modifier_2 ?? ModelConst::DISABLED;
            $model->cts_modifier_3 = $model->cts_modifier_3 ?? ModelConst::DISABLED;
        });
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'cts_id',
        'crt_id',
        'lot_id',
        'cts_tickets',
        'cts_price',
        'cts_ticket_extra',
        'cts_pck_type',
        'cts_ticket_byDraw',
        'cts_draws',
        'cts_ticket_nextDraw',
        'cts_winning_behaviour',
        'cts_renew',
        'cts_prc_id',
        'cts_printable_name',
        'cts_draws_by_ticket',
        'cts_day_to_play',
        'cts_wheel',
        'cts_wheel_balls',
        'cts_wheel_lines',
        'wheel_id',
        'cts_next_draw_id',
        'bonus_id',
        'price_attributes',
        'cart_subscription_picks_attributes',
        'next_draw_attributes',
        'cts_modifier_1',
        'cts_modifier_2',
        'cts_modifier_3',
        'boosted_modifier_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart() {
        return $this->belongsTo(Cart::class, 'crt_id', 'crt_id');
    }

    public function lottery() {
        return $this->belongsTo(Lottery::class, 'lot_id', 'lot_id');
    }

    public function cart_subscription_pick() {
        return $this->hasOne(CartSubscriptionPick::class, 'cts_id', 'cts_id');
    }

    public function first_day_to_play() {
        return $this->hasOne(LotteryFirstDayToPlay::class, 'cts_id', 'cts_id');
    }

    public function cart_subscription_picks() {
        return $this->hasMany(CartSubscriptionPick::class, 'cts_id', 'cts_id');
    }

    public function price() {
        return $this->hasOne(Price::class, 'prc_id', 'cts_prc_id');
    }

    public function next_draw() {
        return $this->belongsTo(Draw::class, 'cts_next_draw_id', 'draw_id');
    }

    public function getPriceAttributesAttribute() {
        $price = $this->cts_prc_id != 0 ? $this->price : null;
        return !is_null($price) ? $price->transformer::transform($price) : $price;
    }

    public function getCartSubscriptionPicksAttributesAttribute() {
        $picks = collect([]);
        $this->cart_subscription_picks->each(function (CartSubscriptionPick $item) use ($picks) {
            $pick = $item->transformer ? $item->transformer::transform($item) : $item;
            $picks->push($pick);
        });
        return $picks;
    }

    public function getNextDrawAttributesAttribute() {
        return $this->next_draw ? $this->next_draw->transformer::transform($this->next_draw) : null;
    }

    public function subscription() {
        return $this->hasOne(LotterySubscription::class, 'cts_id', 'cts_id');
    }

    public function getDurationAttribute() {
        if ($this->price) {
            $measure = '';
            if ($this->price->prc_time_type == 1) {
                if ($this->price->prc_time > 1) {
                    $measure = trans('lang.weeks');
                } else {
                    $measure = trans('lang.week');
                }
            } elseif($this->price->prc_time_type == 0 && $this->price->prc_time > 0) {
                if ($this->price->prc_time > 1) {
                    $measure = trans('lang.months');
                } else {
                    $measure = trans('lang.month');
                }
            } else {
                return null;
            }
            return $this->cts_ticket_byDraw > 0 ? $this->cts_ticket_byDraw.' '.$measure : $this->cts_ticket_byDraw;
        } else {
            return null;
        }

    }

    public function wheel() {
        return $this->hasOne(Wheel::class, 'wheel_id', 'wheel_id');
    }

    public function getWheelAttributesAttribute() {
        return $this->wheel ? $this->wheel->transformer::transform($this->wheel) : null;
    }

    public function getLotteryNameAttribute() {
        return $this->lottery ? $this->lottery->name : null;
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
        if($this->lottery){
            $lotteriesBoostedJackpot = $this->lottery ? $this->lottery->lotteriesBoostedJackpot : null;
            $calculateMountBoostedService = new CalculateMountsBoostedJackpotService();
            $filterLimitJackpot = new FilterBoostedJackpotExceedLimitService($calculateMountBoostedService);
            $filterModifierJackpot = new FilterBoostedJackpotModifierService();
            [ $lotteriesBoostedJackpot, $jackpot, $countValuesActive ] = $filterLimitJackpot->execute(
                $lotteriesBoostedJackpot,
                $this->lottery
            );
            $lotteriesBoostedJackpot = $filterModifierJackpot->execute($lotteriesBoostedJackpot);

            return SetTransformToModelOrCollectionService::execute($lotteriesBoostedJackpot);
        }

        return null;
    }

    public function getLotteryRegionAttribute() {
        return $this->lottery ? $this->lottery->region_attributes : null;
    }

    public function getDrawDateAttribute() {
        return $this->next_draw ? $this->next_draw->draw_date .' '.$this->next_draw->draw_time : null;
    }

    public function getPausedAttribute(){
        return (boolean) $this->cts_ticket_nextDraw == 0 ? true : false;
    }


}
