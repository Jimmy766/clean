<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Models\RaffleTierTemplate;
use Illuminate\Database\Eloquent\Model;

class RaffleTier extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = true;
    protected $table = 'raffle_tier';
//    public $transformer = RaffleTransformer::class;

    public function raffle_tier_templates() {
        return $this->hasMany(RaffleTierTemplate::class, 'id_tier', 'id');
    }

    public function raffleTierTemplates()
    {
        return $this->hasMany(RaffleTierTemplate::class, 'id_tier', 'id')
            ->where('name', 'first')
            ->orWhere('name', 'SECOND');
    }
}
