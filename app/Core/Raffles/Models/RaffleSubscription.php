<?php

namespace App\Core\Raffles\Models;

use App\Core\Carts\Models\CartRaffle;
use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Raffles\Models\RaffleTicket;
use App\Core\Raffles\Transforms\RaffleSubscriptionTransformer;
use App\Core\Raffles\Transforms\RaffleTicketWinningsTransformer;
use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *     definition="RaffleSubscriptionDetail",
 *     @SWG\Property(
 *       property="order",
 *       description="Order identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="order_date",
 *       description="Order date",
 *       type="string",
 *       example="active"
 *     ),
 *     @SWG\Property(
 *       property="identifier",
 *       description="Raffle Subscription identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="draw_identifier",
 *       description="Raffle draw identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="draw_extra_identifier",
 *       description="Raffle draw extra identifier",
 *       type="integer",
 *      example="1234"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       description="Name of Raffle",
 *       type="string",
 *       example="#SPAIN_THURSDAY#"
 *     ),
 *     @SWG\Property(
 *       property="type_tag",
 *       type="string",
 *       description="Raffle type tag",
 *       example="#LOTERIA_NACIONAL_RAFFLE_TYPE1#"
 *     ),
 *     @SWG\Property(
 *       property="prize",
 *       description="Prize",
 *       type="number",
 *       format="float",
 *       example="13.3"
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       description="Currency",
 *       type="string",
 *       example="USD"
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       description="Subscription status",
 *       type="string",
 *       example="active"
 *     ),
 *     @SWG\Property(
 *       property="status_tag",
 *       description="Subscription status tag",
 *       type="string",
 *       example="#SUBSCRIPTION_DETAIL_STATUS_ACTIVE#"
 *     ),
 *     @SWG\Property(
 *       property="draws_emitted",
 *       description="Emitted Draws",
 *       type="integer",
 *       example="2",
 *     ),
 *     @SWG\Property(
 *       property="draws_total",
 *       description="Total Draws",
 *       type="integer",
 *       example="2",
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       description="Tickets Qty",
 *       type="integer",
 *       example="2",
 *     ),
 *     @SWG\Property(
 *       property="tickets_tag",
 *       description="Tickets tag",
 *       type="string",
 *       example="#GORDO_RAFFLE_DEC_DIFF#",
 *     ),
 *     @SWG\Property(
 *       property="tickets_list",
 *       description="Subscription tickets",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/RaffleTicket"),
 *     ),
 *  )
 */



class RaffleSubscription extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'rsub_id';
    protected $table = 'raffles_subscriptions';
    public $timestamps = false;
    public $transformer = RaffleSubscriptionTransformer::class;

    protected $fillable = [

    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];

    public function cart_raffle() {
        return $this->belongsTo(CartRaffle::class, 'crf_id', 'crf_id');
    }

    public function raffle() {
        return $this->belongsTo(Raffle::class, 'inf_id', 'inf_id');
    }

    public function raffle_tickets() {
        return $this->hasMany(RaffleTicket::class, 'rsub_id', 'rsub_id');
    }

    public function last_draw() {
        return $this->hasOne(RaffleDraw::class, 'rff_id', 'rsub_lastdraw_id');
    }

    public function getRaffleNameAttribute() {
        return $this->raffle ? $this->raffle->name : null;
    }

    public function getDrawIdAttribute() {
        return $this->last_draw ? $this->last_draw->rff_id : null;
    }

    public function getDrawExtraIdAttribute() {
        return $this->last_draw ? $this->last_draw->draw_extra_id : null;
    }

    public function getRaffleTypeTagAttribute() {
        return $this->raffle ? $this->raffle->type_tag : null;
    }

    public function getDrawsAttribute() {
        $emitted = null;
        $total = ($this->isExpired()) ? $this->raffle_tickets->groupBy('rff_id')->count() : null;

        if ($this->isActive()) {
            $total = ceil(($this->rsub_tickets + $this->rsub_ticket_extra) / $this->rsub_ticket_byDraw);
            $last_draw = $this->last_draw;
            $emitted = $last_draw && $last_draw->rff_status == 1 ? ceil(($this->rsub_emitted / $this->rsub_ticket_byDraw) - 1) :
                ceil($this->rsub_emitted / $this->rsub_ticket_byDraw);

        }
        $ret = array();
        $ret[0] = $emitted ? ['emitted' => $emitted, 'total' => $total] : ['emitted' => 0,'total' => $total];
        return $ret;
    }

    public function getCurrencyAttribute() {
        return $this->raffle ? $this->raffle->curr_code : null;
    }

    public function isActive() {
        $last_draw = $this->last_draw;
        return $this->rsub_status != 2 && (($this->rsub_tickets + $this->rsub_ticket_extra > $this->rsub_emitted) || ($last_draw && $last_draw->rff_status == 1));
    }

    public function isExpired() {
        $last_draw = $this->last_draw;
        return $last_draw && $last_draw->rff_status != 1 && (($this->rsub_tickets + $this->rsub_ticket_extra == $this->rsub_emitted) || $this->rsub_status == 2);
    }

    public function getPrizesAttribute() {
        $prizes = 0;
        $tickets = $this->raffle_tickets;
        if ($this->isExpired()) {
            $tickets = $tickets->filter(function ($item) {
                return $item->rtck_status != 2;
            });
        }
        $tickets->each(function ($item) use (&$prizes) {
            $prizes += $item->rtck_prize;
        });
        return round($prizes, 2);
    }

    /**
     * @return array
     */
    public function getWinnigsPrizesAttribute() {
        $prizes = [
            'prizes' => 0,
            'rff_id' => '',
            'draw_date' => '',
        ];
        $tickets = $this->tickets_winnings;

        $tickets->each(function ($item) use (&$prizes) {
            $prizes['prizes'] += $item->rtck_prize;
            $prizes['rff_id'] = $item->rff_id;
            $prizes['draw_date'] = $item->draw_date;
        });
        $prizes['prizes'] = round($prizes['prizes'], 2);
        return $prizes;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets_winnings(){
        return $this->raffle_tickets()->where('rtck_status','!=',2)->where('rtck_prize','>',0);
    }

    public function getStatusAttribute() {
        return $this->isActive() ? trans('lang.active_subscription') : ($this->isExpired() ? trans('lang.expired_subscription') : null);
    }

    public function getStatusTagAttribute() {
        return $this->isActive() ? '#SUBSCRIPTION_DETAIL_STATUS_ACTIVE#': ($this->isExpired() ? '#SUBSCRIPTION_DETAIL_STATUS_EXPIRED#' : null);
    }

    public function getTicketsListAttribute() {
        $tickets_list = collect([]);
        $this->raffle_tickets->where('rtck_status', '=', 1)->each(function($item) use ($tickets_list) {
            $tickets_list->push($item->transformer ? $item->transformer::transform($item) : $item);
        });
        return $tickets_list;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getWinnignsTicketsListAttribute() {
        $tickets_list = collect([]);
        $raffles_tickets = $this->tickets_winnings;
        $raffles_tickets->where('rtck_status', '=', 1)->each(function($item) use ($tickets_list) {
            $item->transformer = RaffleTicketWinningsTransformer::class;
            $tickets_list->push($item->transformer ? $item->transformer::transform($item) : $item);
        });
        return $tickets_list;
    }
}
