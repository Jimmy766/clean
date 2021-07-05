<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\SubscriptionsPicksWheelsByDrawTransformer;
use Illuminate\Database\Eloquent\Model;



class SubscriptionsPicksWheelsByDraw extends Model
{
    public $connection = 'mysql_external';
    protected $primaryKey = 'spwb_id';
    public $timestamps = false;
    protected $table = 'subscriptions_picks_wheels_bydraw';
    public $transformer = SubscriptionsPicksWheelsByDrawTransformer::class;

    public function getBallsAttribute() {
        if ($this->picked_balls) {
            $balls = collect([]);
            foreach (explode(',', $this->picked_balls) as $ball) {
                $balls->push((integer)$ball);
            }
            return $balls;
        } else {
            return null;
        }
    }

    public function getExtraBallsAttribute() {
        if ($this->picked_extras!=''){
            $balls = collect([]);
            foreach (explode(',', $this->picked_extras) as $ball) {
                $balls->push((integer)$ball);
            }
            return $balls;
        } else {
            return null;
        }
    }
}
