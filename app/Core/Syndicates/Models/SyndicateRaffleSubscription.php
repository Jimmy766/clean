<?php

namespace App\Core\Syndicates\Models;

use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Syndicates\Models\SyndicateRaffle;
use App\Core\Syndicates\Models\SyndicateRaffleParticipation;
use App\Core\Syndicates\Models\SyndicateRafflePrize;
use App\Core\Syndicates\Transforms\SyndicateRaffleSubscriptionTransformer;
use Illuminate\Database\Eloquent\Model;
use DB;

class SyndicateRaffleSubscription extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'rsyndicate_sub_id';
    protected $table = 'syndicate_raffle_subscriptions';
    public $timestamps = false;
    public $transformer = SyndicateRaffleSubscriptionTransformer::class;

    protected $fillable = [

    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];

    public function raffle() {
        return $this->belongsTo(Raffle::class, 'inf_id', 'inf_id');
    }

    public function getRaffleAttribute() {
        $raffle = $this->raffle_syndicate ? $this->raffle_syndicate : null;
        return [
            'identifier' => $this->inf_id,
            'name' => $raffle ? $raffle->syndicate_raffle_name : null,
            'type_tag' => $raffle->type_tag,
        ];
    }

    public function raffle_syndicate() {
        return $this->belongsTo(SyndicateRaffle::class, 'rsyndicate_id', 'id');
    }

    public function getParticipationsFractionsAttribute()
    {
        $raffleSyndicate = $this->raffle_syndicate;
        if($raffleSyndicate !== null){
           return $raffleSyndicate->participations_fractions;
        }
        return null;
    }

    public function getSyndicateRaffleNameAttribute() {
        $raffle_syndicate = $this->raffle_syndicate;
        return $raffle_syndicate ? $raffle_syndicate->syndicate_raffle_name : null;
    }

    public function last_draw() {
        return $this->hasOne(RaffleDraw::class, 'rff_id', 'sub_lastdraw_id');
    }

    public function isActive() {
        $last_draw = $this->last_draw;
        return $this->sub_status != 2 && (($this->sub_tickets + $this->sub_ticket_extra > $this->sub_emitted) || ($last_draw && $last_draw->rff_status == 1));
    }

    public function isExpired() {
        $last_draw = $this->last_draw;
        return $last_draw && $last_draw->rff_status != 1 && (($this->sub_tickets + $this->sub_ticket_extra == $this->sub_emitted) || $this->sub_status == 2);
    }

    public function getDrawsAttribute() {
        $emitted = null;
        $total = SyndicateRaffleSubscription::where('rsyndicate_cts_id', '=', $this->rsyndicate_cts_id)
            ->select(DB::raw('(sub_tickets + sub_ticket_extra) / sub_ticket_byDraw as drawss'), 'inf_id')
            ->distinct()
            ->get()
            ->sum('drawss');

        $participations = $this->syndicate_raffle_participations->isNotEmpty() ? $this->syndicate_raffle_participations()->distinct('rff_id')->count() : null;

        if ($this->last_draw && $this->last_draw->rff_status == 0) {
            $emitted = $participations ? $participations - 1 : $emitted;
        } else {
            $emitted = $participations ? $participations : $emitted;
        }
        $total = $participations ? $participations + ceil(($this->sub_tickets + $this->sub_ticket_extra - $this->sub_emitted) / $this->sub_ticket_byDraw) : $total;

        $ret = array();
        $ret[0] = $emitted ? ['emitted' => $emitted, 'total' => $total] : ['total' => $total];
        return $ret;
    }

    public function syndicate_raffle_prizes() {
        return $this->hasMany(SyndicateRafflePrize::class, 'rsyndicate_sub_id', 'rsyndicate_sub_id');
    }

    public function getPrizesAttribute() {
        $prizes = 0;
        $this->syndicate_raffle_prizes->each(function ($item) use (&$prizes) {
            $prizes += $item->prize;
        });
        return round($prizes, 2);
    }

    public function getCurrencyAttribute() {
        return request()->user()->curr_code;
    }

    public function getStatusAttribute() {
        return $this->isActive() ? trans('lang.active_subscription') : ($this->isExpired() ? trans('lang.expired_subscription') : null);
    }

    public function getStatusTagAttribute() {
        return $this->isActive() ? '#SUBSCRIPTION_DETAIL_STATUS_ACTIVE#': ($this->isExpired() ? '#SUBSCRIPTION_DETAIL_STATUS_EXPIRED#' : null);
    }

    public function syndicate_raffle_participations() {
        return $this->hasMany(SyndicateRaffleParticipation::class, 'rsyndicate_sub_id', 'rsyndicate_sub_id');
    }

    public function getParticipationsAttribute() {
        $participations = collect([]);
        $this->syndicate_raffle_participations->each(function ($item) use ($participations) {
            $participation = $item->transformer ? $item->transformer::transform($item) : $item;
            $participations->push($participation);
        });
        return $participations;
    }
}
