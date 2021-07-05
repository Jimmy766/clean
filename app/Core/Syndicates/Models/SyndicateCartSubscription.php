<?php

namespace App\Core\Syndicates\Models;

use App\Core\Carts\Models\Cart;
use App\Core\Rapi\Models\Bonus;
use App\Core\Rapi\Services\GetParticipationFractionsService;
use App\Core\Syndicates\Models\Syndicate;
use App\Core\Syndicates\Models\SyndicatePrice;
use App\Core\Syndicates\Models\SyndicateSubscription;
use App\Core\Syndicates\Models\SyndicateWheelsPicks;
use App\Core\Syndicates\Transforms\SyndicateCartSubscriptionTransformer;
use App\Core\Syndicates\Transforms\SyndicateCartTransformer;
use App\Core\Telem\Transforms\TelemSyndicateWheelCartTransformer;
use Illuminate\Database\Eloquent\Model;
use App\Core\Base\Traits\LogCache;


class SyndicateCartSubscription extends Model
{

    use LogCache;

    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cts_id';
    public $timestamps = false;
    public $transformer = SyndicateCartSubscriptionTransformer::class;
    protected $active = false;

    protected $fillable = [
        'crt_id',
        'syndicate_id',
        'cts_price',
        'sub_id',
        'cts_ticket_extra',
        'cts_ticket_byDraw',
        'cts_ticket_nextDraw',
        'cts_renew',
        'cts_syndicate_prc_id',
        'syndicate_picks_id',
        'bonus_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'cts_id',
        'crt_id',
        'syndicate_id',
        'cts_price',
        'sub_id',
        'cts_ticket_extra',
        'cts_ticket_byDraw',
        'cts_ticket_nextDraw',
        'cts_renew',
        'cts_syndicate_prc_id',
        'syndicate_picks_id',
        'bonus_id',
    ];

    public function cart() {
        return $this->belongsTo(Cart::class, 'crt_id', 'crt_id');
    }

    public function price() {
        return $this->hasOne(SyndicatePrice::class, 'prc_id', 'cts_syndicate_prc_id');
    }

    public function wheel_picks() {
        return $this->hasOne(SyndicateWheelsPicks::class,
            'syndicate_picks_id', 'syndicate_picks_id');
    }

    public function getPriceAttributesAttribute() {
        $price = $this->cts_syndicate_prc_id != 0 ? $this->price : null;
        return !is_null($price) ? $price->transformer::transform($price) : $price;
    }

    public function getWheelPicksAttributesAttribute(){
        $wheel_picks = $this->wheel_picks;
        return !is_null($wheel_picks) ? $wheel_picks->transformer::transform($wheel_picks) : $wheel_picks;
    }

    public function syndicate() {
        return $this->belongsTo(Syndicate::class, 'syndicate_id', 'id');
    }

    public function getParticipationsAttribute()
    {
        $syndicate = $this->syndicate;
        return GetParticipationFractionsService::execute($syndicate, $this->cts_ticket_byDraw);
    }

    public function getSyndicateAttributesAttribute() {
        if ($this->syndicate) {
            if($this->syndicate->has_wheel){
                $this->syndicate->transformer = TelemSyndicateWheelCartTransformer::class;
            }else{
                $this->syndicate->transformer = SyndicateCartTransformer::class;
            }

            return $this->syndicate->transformer::transform($this->syndicate);
        }
        return null;
    }

    public function syndicate_subscriptions() {
        return $this->hasMany(SyndicateSubscription::class, 'syndicate_cts_id', 'cts_id');
    }

    public function syndicate_subscriptions_list() {
        return $this->syndicate_subscriptions->unique('lot_id');
    }

    public function getPurchaseDateAttribute() {
        return $this->syndicate_subscriptions_list()->isNotEmpty() ? $this->syndicate_subscriptions_list()->first()->sub_buydate : null;
    }

    public function getSubscriptionsAttribute() {
        $syndicate_subscriptions = $this->syndicate_subscriptions_list();
        if ($syndicate_subscriptions->isNotEmpty() && $syndicate_subscriptions->count()==1) {
            $syndicate = $syndicate_subscriptions->first();
            return GetParticipationFractionsService::execute($syndicate, $syndicate->sub_ticket_byDraw);
        } else {
            $syndicate = $this->syndicate;
            return GetParticipationFractionsService::execute($syndicate, $this->cts_ticket_byDraw);
        }
    }

    public function getStatusAttribute() {
        $this->syndicate_subscriptions_list()->each(function ($item, $key) {
            if ($item->isActive()) {
                return !$this->active = true;
            }
        });
        return $this->active ? trans('lang.active_subscription') : trans('lang.expired_subscription');
    }

    public function getStatusTagAttribute() {
        $this->syndicate_subscriptions_list()->each(function ($item, $key) {
            if ($item->isActive()) {
                return !$this->active = true;
            }
        });
        return $this->active ? trans('lang.active_subscription_tag') : trans('lang.expired_subscription_tag');
    }

    public function getDrawsAttribute() {
        $syndicate_subscriptions = $this->syndicate_subscriptions_list();
        $total = $syndicate_subscriptions->sum(function ($item) {
            if(!empty($item->sub_ticket_byDraw)){
                return ($item->sub_tickets + $item->sub_ticket_extra) / $item->sub_ticket_byDraw;
            }
            return 0;
        });
        $emitted = null;
        if ($this->syndicate->multi_lotto != 1 && $this->active && !empty($item->sub_ticket_byDraw)) {
            $syndicate_subscription = $syndicate_subscriptions->first();
            $participations = $syndicate_subscription->syndicate_participations->isNotEmpty() ?
                $syndicate_subscription->syndicate_participations()->distinct('draw_id')->count() : null;

            if ($syndicate_subscription->last_draw && $syndicate_subscription->last_draw->draw_status == 0) {
                $emitted = ceil($syndicate_subscription->sub_emitted / $syndicate_subscription->sub_ticket_byDraw) - 1;
                $emitted = $participations ? $participations - 1 : $emitted;
            } else {
                $emitted = ceil($syndicate_subscription->sub_emitted / $syndicate_subscription->sub_ticket_byDraw);
                $emitted = $participations ? $participations : $emitted;
            }
            $total = $participations ? $participations + ceil(($syndicate_subscription->sub_tickets +
                        $syndicate_subscription->sub_ticket_extra - $syndicate_subscription->sub_emitted)
                    / $syndicate_subscription->sub_ticket_byDraw) : $total;
        }
        return !is_null($emitted) ? ['emitted' => (integer)$emitted, 'total' => $total] : ['total' => $total];
    }

    public function getPrizesAttribute() {
        $syndicate_subscriptions = $this->syndicate_subscriptions_list();
        $prizes = [];
        $syndicate_subscriptions->each(function ($item) use (&$prizes) {
            foreach ($item->prizes as $prize) {
                if (isset($prizes[$prize['currency']])) {
                    $prizes[$prize['currency']] += round($prize['prize'], 2);
                } else {
                    $prizes[$prize['currency']] = round($prize['prize'], 2);
                }
            }
        });
        $prize_array = [];
        $prizes = collect($prizes);
        $prizes->each(function ($item, $key) use (&$prize_array) {
            $prize_array []= ['currency' => $key, 'prize' => round($item,2)];
        });
        return $prize_array;
    }

    public function getDurationAttribute() {
        $price = $this->price ? $this->price : null;
        if ($price) {
            $measure = '';
            if ($price->prc_time_type == 1) {
                if ($price->prc_time > 1) {
                    $measure = trans('lang.weeks');
                } else {
                    $measure = trans('lang.week');
                }
            } elseif($price->prc_time_type == 0 && $price->prc_time > 0) {
                if ($price->prc_time > 1) {
                    $measure = trans('lang.months');
                } else {
                    $measure = trans('lang.month');
                }
            } else {
                return null;
            }
            return $this->prc_time > 0 ? $this->prc_time.' '.$measure : $this->prc_time;
        } else {
            return null;
        }

    }

    public function getGamesAttribute() {
        $syndicate = $this->syndicate ? $this->syndicate : null;
        if ($syndicate && $syndicate->multi_lotto == 0) {
            $syndicate_lotto = $syndicate ? $syndicate->syndicate_lotteries->first() : null;
            $sub_tickets = $syndicate_lotto ? $syndicate_lotto->tickets : 0;
            return ceil(($sub_tickets + $this->cts_ticket_extra) / $this->cts_ticket_byDraw);
        }
        return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bonus() {
        return $this->belongsTo(Bonus::class, 'bonus_id', 'id');
    }

    /**
     * @return mixed
     */
    public function getBonusProductsAttribute() {
        $bonus = $this->bonus;
        return $bonus ? $bonus->bonus_products_detail : [];
    }
}
