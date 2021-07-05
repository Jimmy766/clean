<?php

namespace App\Core\Carts\Models;

use App\Core\Raffles\Models\Raffle;
use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Raffles\Models\RafflePrice;
use App\Core\Raffles\Models\RaffleSubscription;
use App\Core\Carts\Transforms\CartRaffleTransformer;
use App\Core\Raffles\Transforms\RaffleTransformer;
use Illuminate\Database\Eloquent\Model;

class CartRaffle extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'crf_id';
    protected $table = 'cart_raffles';
    public $timestamps = false;
    public $transformer = CartRaffleTransformer::class;

    protected $fillable = [
        'crt_id',
        'inf_id',
        'rff_id',
        'rtck_blocks',
        'crf_tickets',
        'crf_ticket_byDraw',
        'crf_ticket_nextDraw',
        'crf_price',
        'crf_play_method',
        'crf_printable_name',
        'crf_prc_rff_id',
        'crf_renew',
        'rsub_id',
        'bonus_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'crf_id',
        'crt_id',
        'inf_id',
        'rff_id',
        'rtck_blocks',
        'crf_tickets',
        'crf_ticket_byDraw',
        'crf_ticket_nextDraw',
        'crf_price',
        'crf_play_method',
        'crf_printable_name',
        'crf_prc_rff_id',
        'crf_renew',
        'rsub_id',
        'bonus_id',
    ];

    public function cart() {
        return $this->belongsTo(Cart::class, 'crt_id', 'crt_id');
    }

    public function price() {
        return $this->hasOne(RafflePrice::class, 'prc_rff_id', 'crf_prc_rff_id');
    }

    public function getPriceAttributesAttribute() {
        $price = $this->crf_prc_rff_id != 0 ? $this->price : null;
        return !is_null($price) ? $price->transformer::transform($price) : $price;
    }

    public function getRaffleAttributesAttribute() {
        $raffle = $this->raffleFromCartRaffles;
        return $raffle !== null ? (new RaffleTransformer)->transform($raffle) :
            null;
    }

    public function raffle_subscription() {
        return $this->hasOne(RaffleSubscription::class, 'crf_id', 'crf_id');
    }

    public function raffle_draw() {
        return $this->belongsTo(RaffleDraw::class, 'rff_id', 'rff_id');
    }

    public function raffleFromCartRaffles() {
        return $this->belongsTo(Raffle::class, 'inf_id', 'inf_id');
    }

    public function tickets() {
        $tickets = 0;
        $tickets_tag = '';
        $raffle_subscription = $this->raffle_subscription;
        if ($this->raffle_draw && $this->raffle_draw->rff_ticket_type == 0) {
            $tickets = $raffle_subscription ? $raffle_subscription->rsub_ticket_byDraw : null;
            $tickets_tag = $tickets == 1 ? '#RAFFLES_TICKET#' : '#RAFFLES_TICKETS#';
        } else {
            if($this->crf_play_method == 0 || $this->crf_play_method == 3) {
                $tickets = $this->crf_tickets;
                $tickets_tag = '#GORDO_RAFFLE_DEC_DIFF#';
            } elseif ($this->crf_play_method == 2) {
                $tickets = $this->crf_tickets;
                $tickets_tag = '#GORDO_RAFFLE_DEC#';
            } elseif ($this->crf_play_method == 1) {
                $tickets = $this->crf_tickets / 10;
                $tickets_tag = '#GORDO_RAFFLE_ENT#';
            } elseif ($this->crf_play_method == 4) {
                $tickets = $raffle_subscription ? $raffle_subscription->rsub_ticket_byDraw : null;
                $tickets_tag = '#RAFFLE_GENERAL_TWENTIETH#';
            }
        }
        return ['tickets' => $tickets, 'tickets_tag' => $tickets_tag];
    }
}
