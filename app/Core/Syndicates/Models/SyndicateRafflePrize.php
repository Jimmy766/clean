<?php

namespace App\Core\Syndicates\Models;

use App\Core\Raffles\Models\RaffleTicket;
use App\Core\Syndicates\Models\SyndicateRaffleSubscription;
use App\Core\Raffles\Transforms\RaffleTicketWinningsTransformer;
use Illuminate\Database\Eloquent\Model;

class SyndicateRafflePrize extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rtck_id',
        'usr_id',
        'prize',
        'rsyndicate_sub_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'tck_id',
        'usr_id',
        'prize',
        'rsyndicate_sub_id',
    ];

    public function syndicate_raffle_subscriptions() {
        return $this->belongsTo(SyndicateRaffleSubscription::class, 'rsyndicate_sub_id','rsyndicate_sub_id');
    }

    public function raffle_ticket(){
        return $this->belongsTo(RaffleTicket::class,'rtck_id','rtck_id');
    }

    public function raffle_tickets(){
        return $this->hasMany(RaffleTicket::class,'rtck_id','rtck_id');
    }

    public function getTicketsListAttribute() {
        $tickets_list = collect([]);
        $raffles_tickets = $this->raffle_tickets;
        $raffles_tickets->first()->transformer = RaffleTicketWinningsTransformer::class;
        $raffles_tickets->where('rtck_status', '=', 1)->each(function($item) use ($tickets_list) {
            $item->rtck_prize = $this->prize;
            $tickets_list->push($item->transformer ? $item->transformer::transform($item) : $item);
        });
        return $tickets_list;
    }

}
