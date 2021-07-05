<?php

namespace App\Core\Syndicates\Models;

use App\Core\Raffles\Models\Raffle;
use Illuminate\Database\Eloquent\Model;

class SyndicateRaffleRaffle extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'id';
    protected $table = 'syndicate_raffle_raffles';
    public $timestamps = false;

    public function raffle() {
        return $this->hasOne(Raffle::class, 'inf_id', 'inf_id');
    }
}
