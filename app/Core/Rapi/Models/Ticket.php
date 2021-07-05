<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Traits\LogCache;
use App\Core\Lotteries\Models\LotterySubscription;
use App\Core\Rapi\Transforms\TicketTransformer;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{

    use LogCache;

    public $connection = 'mysql_external';
    protected $primaryKey = 'tck_id';
    public $timestamps = false;
    public $transformer = TicketTransformer::class;
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

    public function subscription() {
        return $this->belongsTo(LotterySubscription::class, 'sub_id', 'sub_id');
    }

    public function draw() {
        return $this->belongsTo(Draw::class, 'draw_id', 'draw_id');
    }

    public function getLineBallsAttribute() {
        $lot_ballss = collect([]);
        $lot_balls = $this->draw->lottery->lot_pick_balls;
        if ($this->draw->lot_id === 14 && $this->draw_id <= 9764) {
            $lot_balls = 5;
        }
        if ($this->draw->lot_id === 15 && $this->draw_id <= 10166) {
            $lot_balls = 6;
        }
        for ($i = 1; $i <= $lot_balls; $i++) {
            $ball = 'tck_n'.$i;
            $lot_ballss->push($this->$ball);
        }
        return $lot_ballss;
    }

    public function getLineExtraBallsAttribute() {
        $extra_balls = collect([]);
        $lot_balls = $this->draw->lottery->lot_pick_balls;
        $lot_extra = $this->draw->lottery->lot_pick_extra;
        if ($this->draw->lot_id === 14 && $this->draw_id <= 9764) {
            $lot_balls = 5;
            $lot_extra = 1;
        }
        if ($this->draw->lot_id === 15 && $this->draw_id <= 10166) {
            $lot_balls = 6;
            $lot_extra = 2;
        }
        for ($i = $lot_balls + 1; $i <= $lot_extra + $lot_balls; $i++) {
            $ball = 'tck_n'.$i;
            $extra_balls->push($this->$ball);
        }
        return $extra_balls;
    }

    public function getLineRefundBallsAttribute() {
        $refund_balls = collect([]);
        $lot_balls = $this->draw->lottery->lot_pick_balls;
        $lot_extra = $this->draw->lottery->lot_pick_extra;
        $lot_refund = $this->draw->lottery->lot_reintegro;
        if ($this->draw->lot_id === 14 && $this->draw_id <= 9764) {
            $lot_balls = 5;
            $lot_extra = 1;
        }
        if ($this->draw->lot_id === 15 && $this->draw_id <= 10166) {
            $lot_balls = 6;
            $lot_extra = 2;
        }
        for ($i = $lot_balls + $lot_extra + 1; $i <= $lot_extra + $lot_balls + $lot_refund; $i++) {
            $ball = 'tck_n'.$i;
            $refund_balls->push($this->$ball);
        }
        return $refund_balls;
    }

    public function getMatchBallsAttribute() {
        $matchs = 0;
        if ($this->draw->has_results()) {
            $this->draw->lot_balls->each(function($item, $key) use (&$matchs) {
                if ($this->line_balls->contains($item)) {
                    $matchs++;
                }
            });
        }
        return $matchs;
    }

    public function getMatchExtraBallsAttribute() {
        $matchs = 0;
        if ($this->draw->has_results()) {
            //busco extra en normales
            if ($this->line_extra_balls->isEmpty()) {
                $this->draw->extra_balls->each(function($item, $key) use (&$matchs) {
                    if ($this->line_balls->contains($item)) {
                        $matchs++;
                    }
                });
            } else {
                $this->draw->extra_balls->each(function($item, $key) use (&$matchs) {
                    if ($this->line_extra_balls->contains($item)) {
                        $matchs++;
                    }
                });
            }

            if ($this->draw->has_results()) {
                $this->draw->refund_balls->each(function ($item, $key) use (&$matchs) {
                    if ($this->line_refund_balls->contains($item)) {
                        $matchs++;
                    }
                });
            }
        }
        return $matchs;
    }

    public function getRaffleAttribute() {
        $subscription = $this->subscription;
        $lottery = $subscription ? $subscription->lottery : null;
        if ($lottery && $lottery->lot_raffle_number == 1 && ($lottery->lot_id != 8 || ($this->draw_id < 39014 && $lottery->lot_id == 8))) {
            return $this->tck_raffle1;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getStatusTagAttribute(){
        switch ($this->tck_status){
            case 1:
            case 5:
                $status_tag = '#WINNINGS_DETAIL_PENDING_CLAIM#';
                break;
            case 2:
                $status_tag = '#WINNINGS_DETAIL_PENDING_APPROVAL#';
                break;
            case 3:
                $status_tag = '#WINNINGS_DETAIL_CHANGED#';
                break;
            case 4:
            case 6:
                $status_tag = '#WINNINGS_DETAIL_CREDIT#';
                break;
        }
        return $status_tag;
    }

}
