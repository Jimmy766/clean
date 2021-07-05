<?php

namespace App\Core\Syndicates\Models;

use App\Core\Rapi\Models\Draw;
use App\Core\Syndicates\Models\SyndicatePrize;
use App\Core\Rapi\Models\Ticket;
use App\Core\Syndicates\Transforms\SyndicateParticipationTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Core\Base\Traits\LogCache;


class SyndicateParticipation extends Model
{

    use LogCache;

    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'syndicate_participation';
    public $timestamps = false;
    public $transformer = SyndicateParticipationTransformer::class;
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

    public function ticket_draw() {
        return $this->belongsTo(Draw::class, 'draw_id', 'draw_id');
    }

    public function ticket_sub(){
        return $this->hasMany(Ticket::class, 'draw_id',  'draw_id');
    }

    public function syndicate_prizes(){
        return $this->hasMany(SyndicatePrize::class, 'syndicate_sub_id',  'syndicate_sub_id');
    }

    public function tickets() {
        $tickets = collect([]);
        /*Ticket::where('draw_id', '=', $this->draw_id)*/

        $tk = $this->ticket_sub->where('sub_id', '=', $this->sub_id);

        $tk->each(function ($item) use ($tickets) {
            $ticket = $item->transformer ? $item->transformer::transform($item) : $item;

            $data = $this->syndicate_prizes
                ->where('tck_id', '=', $ticket['identifier'])
                ->where('usr_id', '=', $this->usr_id)
                ->first();

            if ($data) {
                $ticket['winnings'] = round((float)$data->prize,2);
                $ticket['curr_code'] = $data->prize_currency;
            }

            $tickets->push($ticket);

        });
        return $tickets;
    }

    public function getDrawAttribute() {

        $draw = $this->ticket_draw ? $this->ticket_draw : null;

        return $draw ? [
            'identifier' => (integer)$draw->draw_id,
            'date' => $draw->draw_date,
            'results' => $draw->has_results() ? [
                'pick_balls' => $draw->lot_balls,
                'extra_balls' => $draw->extra_balls,
                //'refund_balls' => $draw->refund_balls,
            ] : null,
        ] : null;
    }
}
