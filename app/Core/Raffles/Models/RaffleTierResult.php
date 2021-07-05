<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Models\RaffleTierTemplate;
use Illuminate\Database\Eloquent\Model;

class RaffleTierResult extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    protected $table = 'raffle_tier_results';
//    public $transformer = RaffleTransformer::class;


    public function raffle_tier_template() {
        return $this->belongsTo(RaffleTierTemplate::class, 'id_rff_tier_tpl', 'id');
    }
}
