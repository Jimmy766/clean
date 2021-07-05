<?php

namespace App\Core\Lotteries\Models;

use App\Core\Lotteries\Models\LotterySubscription;
use App\Core\Lotteries\Transforms\LotterySubscriptionPickTransformer;
use Illuminate\Database\Eloquent\Model;


class LotterySubscriptionPick extends Model
{
    public $connection = 'mysql_external';
    protected $primaryKey = 'pck_id';
    public $timestamps = false;
    protected $table = 'subscriptions_picks';
    public $transformer = LotterySubscriptionPickTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sub_id',
        'pck_1',
        'pck_2',
        'pck_3',
        'pck_4',
        'pck_5',
        'pck_6',
        'pck_7',
        'pck_8',
        'pck_9',
        'pck_10',
        'pck_11',
        'pck_12',
        'pck_13',
        'pck_14',
        'pck_15',
        'pck_printed',
        'pck_playing',
        'pck_raffle_number',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'pck_id',
        'sub_id',
        'pck_1',
        'pck_2',
        'pck_3',
        'pck_4',
        'pck_5',
        'pck_6',
        'pck_7',
        'pck_8',
        'pck_9',
        'pck_10',
        'pck_11',
        'pck_12',
        'pck_13',
        'pck_14',
        'pck_15',
        'pck_printed',
        'pck_playing',
        'pck_raffle_number',
    ];

    public function lottery_subscription() {
        return $this->belongsTo(LotterySubscription::class, 'sub_id', 'sub_id');
    }

    public function getLotBallsAttribute() {
        $lot_ballss = collect([]);
        $lottery_subscription = $this->lottery_subscription ? $this->lottery_subscription : null;
        $lottery = $lottery_subscription ? $lottery_subscription->lottery ? $lottery_subscription->lottery : null : null;
        $draw_id = $lottery_subscription ? $lottery_subscription->sub_lastdraw_id : null;
        if ($lottery) {
            $lot_balls = $lottery->lot_pick_balls;
            if ($lottery->lot_id === 14 && $draw_id <= 9764) {
                $lot_balls = 5;
            }
            if ($lottery->lot_id === 15 && $draw_id <= 10166) {
                $lot_balls = 6;
            }
            for ($i = 1; $i <= $lot_balls; $i++) {
                $ball = 'pck_'.$i;
                $lot_ballss->push($this->$ball);
            }
        }
        return $lot_ballss;
    }

    public function getExtraBallsAttribute() {
        $extra_balls = collect([]);
        $lottery_subscription = $this->lottery_subscription ? $this->lottery_subscription : null;
        $lottery = $lottery_subscription ? $lottery_subscription->lottery ? $lottery_subscription->lottery : null : null;
        $draw_id = $lottery_subscription ? $lottery_subscription->sub_lastdraw_id : null;

        $lot_balls = $lottery->lot_pick_balls;
        $lot_extra = $lottery->lot_pick_extra;
        if ($lottery->lot_id === 14 && $draw_id <= 9764) {
            $lot_balls = 5;
            $lot_extra = 1;
        }
        if ($lottery->lot_id === 15 && $draw_id <= 10166) {
            $lot_balls = 6;
            $lot_extra = 2;
        }
        for ($i = $lot_balls + 1; $i <= $lot_extra + $lot_balls; $i++) {
            $ball = 'pck_'.$i;
            $extra_balls->push($this->$ball);
        }
        return $extra_balls;
    }
}
