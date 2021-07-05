<?php

namespace App\Core\Syndicates\Models;

use App\Core\Raffles\Models\RaffleDraw;
use App\Core\Raffles\Models\RaffleTicket;
use App\Core\Syndicates\Transforms\SyndicateRaffleParticipationTransformer;
use Illuminate\Database\Eloquent\Model;

class SyndicateRaffleParticipation extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'syndicate_raffle_participation';
    public $timestamps = false;
    public $transformer = SyndicateRaffleParticipationTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];

    public function ticket_raffle_draw() {
        return $this->belongsTo(RaffleDraw::class, 'rff_id', 'rff_id');
    }

    public function raffle_tickets() {
        $raffle_tickets = collect([]);
        RaffleTicket::where('rff_id', '=', $this->rff_id)->where('rsub_id', '=', $this->rsub_id)->get()
            ->each(function ($item) use ($raffle_tickets) {
            $raffle_ticket = $item->transformer ? $item->transformer::transform($item) : $item;
            $raffle_tickets->push($raffle_ticket);
        });
        return $raffle_tickets;
    }

    public function getRaffleDrawAttribute() {
        $draw = $this->ticket_raffle_draw ? $this->ticket_raffle_draw : null;
        return $draw ? [
            'identifier' => (integer)$draw->rff_id,
            'date' => $draw->rff_playdate,
        ] : null;
    }
}
