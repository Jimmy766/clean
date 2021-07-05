<?php

namespace App\Core\Carts\Models;

use App\Core\Rapi\Services\GetParticipationFractionsService;
use App\Core\Syndicates\Models\SyndicateRaffle;
use App\Core\Syndicates\Models\SyndicateRafflePrice;
use App\Core\Syndicates\Models\SyndicateRaffleSubscription;
use App\Core\Carts\Transforms\CartRaffleSyndicateTransformer;
use Illuminate\Database\Eloquent\Model;

class CartRaffleSyndicate extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cts_id';
    protected $table = 'syndicate_cart_raffles';
    public $timestamps = false;
    public $transformer = CartRaffleSyndicateTransformer::class;
    protected $active = false;

    protected $fillable = [
        'crt_id',
        'rsyndicate_id',
        'cts_price',
        'rsub_id',
        'cts_ticket_extra',
        'cts_ticket_byDraw',
        'cts_ticket_nextDraw',
        'cts_renew',
        'cts_syndicate_prc_id',
        'cts_play_same_group',
        'rsyndicate_picks_id',
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
        'rsyndicate_id',
        'cts_price',
        'rsub_id',
        'cts_ticket_extra',
        'cts_ticket_byDraw',
        'cts_ticket_nextDraw',
        'cts_renew',
        'cts_syndicate_prc_id',
        'cts_play_same_group',
        'rsyndicate_picks_id',
        'bonus_id',
    ];

    public function cart() {
        return $this->belongsTo(Cart::class, 'crt_id', 'crt_id');
    }

    public function price() {
        return $this->hasOne(SyndicateRafflePrice::class, 'prc_id', 'cts_syndicate_prc_id');
    }

    public function getSyndicateRafflePriceAttribute() {
        $price = $this->cts_syndicate_prc_id != 0 ? $this->price : null;
        return !is_null($price) ? $price->transformer::transform($price) : $price;
    }

    public function syndicate_raffle_subscription() {
        return $this->hasOne(SyndicateRaffleSubscription::class, 'rsyndicate_cts_id','cts_id');
    }

    public function syndicate_raffle_subscriptions() {
        return $this->hasMany(SyndicateRaffleSubscription::class, 'rsyndicate_cts_id','cts_id');
    }

    public function syndicate_raffle_subscriptions_list() {
        return $this->syndicate_raffle_subscriptions->unique('inf_id');
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

    public function syndicate_raffle() {
        return $this->belongsTo(SyndicateRaffle::class, 'rsyndicate_id','id');
    }

    public function getRaffleSyndicateAttribute() {
        return $this->syndicate_raffle ? $this->syndicate_raffle->transformer::transform($this->syndicate_raffle) : null;
    }

    public function getGamesAttribute() {
        $syndicate_raffle = $this->syndicate_raffle ? $this->syndicate_raffle : null;
        if ($syndicate_raffle && $syndicate_raffle->multi_raffle == 0) {
            $syndicate_raffle_subscription = $this->syndicate_raffle_subscription;
            $sub_tickets = $syndicate_raffle_subscription ? $syndicate_raffle_subscription->sub_tickets : 0;
            $sub_tickets_extra = $syndicate_raffle_subscription ? $syndicate_raffle_subscription->sub_tickets_extra : 0;
            return ceil(($sub_tickets + $sub_tickets_extra) / $this->cts_ticket_byDraw);
        }
        return null;
    }

    public function getParticipationsAttribute()
    {
        $syndicateRaffle = $this->syndicate_raffle;
        return GetParticipationFractionsService::execute($syndicateRaffle, $this->cts_ticket_byDraw);
    }

    public function getPurchaseDateAttribute() {
        return $this->syndicate_raffle_subscriptions_list()->isNotEmpty() ? $this->syndicate_raffle_subscriptions_list()->first()->sub_buydate : null;
    }

    public function getPrizesAttribute() {
        $syndicate_raffle_subscriptions = $this->syndicate_raffle_subscriptions_list();
        $prizes = 0;
        $syndicate_raffle_subscriptions->each(function (SyndicateRaffleSubscription $item) use (&$prizes) {
            $prizes += $item->prizes;
        });
        return round($prizes, 2);
    }

    public function getSubscriptionsAttribute() {
        $syndicate_raffle_subscriptions = $this->syndicate_raffle_subscriptions_list();
        if ($syndicate_raffle_subscriptions->isNotEmpty() && $syndicate_raffle_subscriptions->count() == 1) {
            $syndicateRaffle = $syndicate_raffle_subscriptions->first();
            return GetParticipationFractionsService::execute($syndicateRaffle, $syndicateRaffle->sub_ticket_byDraw);
        } else {
            $syndicateRaffle = $this->syndicate_raffle;
            return GetParticipationFractionsService::execute($syndicateRaffle, $this->cts_ticket_byDraw);
        }
    }

    public function getStatusAttribute() {
        $this->syndicate_raffle_subscriptions_list()->each(function (SyndicateRaffleSubscription $item) {
            if ($item->isActive()) {
                return !$this->active = true;
            }
        });
        return $this->active ? trans('lang.active_subscription') : trans('lang.expired_subscription');
    }

    public function isActive() {
        $syndicate_raffle_subscriptions = $this->syndicate_raffle_subscriptions_list()->filter(function (SyndicateRaffleSubscription $item) {
            if ($item->isActive()) {
                return true;
            }
            return false;
        });
        return $syndicate_raffle_subscriptions->isNotempty() ? true : false;
    }

    public function isExpired() {
        $quantity = $this->syndicate_raffle_subscriptions_list()->count();
        $syndicate_raffle_subscriptions = $this->syndicate_raffle_subscriptions_list()->filter(function (SyndicateRaffleSubscription $item) {
            if ($item->isExpired()) {
                return true;
            }
            return false;
        });
        return $syndicate_raffle_subscriptions->count() == $quantity ? true : false;
    }

    public function getStatusTagAttribute() {
        $this->syndicate_raffle_subscriptions_list()->each(function (SyndicateRaffleSubscription $item) {
            if ($item->isActive()) {
                return !$this->active = true;
            }
        });
        return $this->active ? trans('lang.active_subscription_tag') : trans('lang.expired_subscription_tag');
    }

    public function getDrawsAttribute() {
        $raffle_syndicate_subscriptions = $this->syndicate_raffle_subscriptions_list();
        $total = $raffle_syndicate_subscriptions->sum(function ($item) {
            if(empty($item->sub_ticket_byDraw)){
                return 0;
            }
            $division = ($item->sub_tickets + $item->sub_ticket_extra) / $item->sub_ticket_byDraw;
            return  $division;
        });
        $emitted = null;
        if ($this->syndicate_raffle->multi_raffle != 1 && $this->active) {
            $raffle_syndicate_subscription = $raffle_syndicate_subscriptions->first();
            $participations = $raffle_syndicate_subscription->syndicate_raffle_participations->isNotEmpty() ?
                $raffle_syndicate_subscription->syndicate_raffle_participations()->distinct('rff_id')->count() : null;

            if ($raffle_syndicate_subscription->last_draw && $raffle_syndicate_subscription->last_draw->draw_status == 0) {
                $emitted = ceil($raffle_syndicate_subscription->sub_emitted / $raffle_syndicate_subscription->sub_ticket_byDraw) - 1;
                $emitted = $participations ? $participations - 1 : $emitted;
            } else {
                $emitted = 0;
                if(!empty($raffle_syndicate_subscription->sub_ticket_byDraw)){
                    $emitted = ceil($raffle_syndicate_subscription->sub_emitted / $raffle_syndicate_subscription->sub_ticket_byDraw);
                }
                $emitted = $participations ? $participations : $emitted;
            }

            $division = 0;
            if(!empty($raffle_syndicate_subscription->sub_ticket_byDraw)){
                $division = ceil(($raffle_syndicate_subscription->sub_tickets +
                        $raffle_syndicate_subscription->sub_ticket_extra - $raffle_syndicate_subscription->sub_emitted)
                    / $raffle_syndicate_subscription->sub_ticket_byDraw);
            }
            $total = $participations ? $participations + $division : $total;
        }
        return !is_null($emitted) ? ['emitted' => (integer)$emitted, 'total' => $total] : ['total' => $total];
    }

}
