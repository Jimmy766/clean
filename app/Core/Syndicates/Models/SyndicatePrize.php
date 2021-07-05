<?php

namespace App\Core\Syndicates\Models;

use App\Core\Syndicates\Models\SyndicateSubscription;
use App\Core\Rapi\Models\Ticket;
use Illuminate\Database\Eloquent\Model;

class SyndicatePrize extends Model
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
        'tck_id',
        'usr_id',
        'prize',
        'syndicate_sub_id',
        'prize_regdate',
        'prize_currency',
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
        'syndicate_sub_id',
        'prize_regdate',
        'prize_currency',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function syndicate_subscription(){
        return $this->belongsTo(SyndicateSubscription::class,'syndicate_sub_id','syndicate_sub_id');
    }

    public function ticket(){
        return $this->belongsTo(Ticket::class,'tck_id','tck_id');
    }

    public function getDrawDateAttribute() {
        $ticket = $this->ticket;
        $draw = $ticket ? $ticket->draw : null;
        return $draw ? $draw->draw_date : null;
    }

}
