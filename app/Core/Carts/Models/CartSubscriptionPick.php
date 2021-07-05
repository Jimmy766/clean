<?php

namespace App\Core\Carts\Models;

use App\Core\Base\Services\ClientService;
use App\Core\Carts\Transforms\CartSubscriptionPickTransformer;
use Illuminate\Database\Eloquent\Model;

class CartSubscriptionPick extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'ctpck_id';
    public $timestamps = false;
    protected $table = 'cart_subscriptions_picks';
    public $transformer = CartSubscriptionPickTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cts_id',
        'ctpck_1',
        'ctpck_2',
        'ctpck_3',
        'ctpck_4',
        'ctpck_5',
        'ctpck_6',
        'ctpck_7',
        'ctpck_8',
        'ctpck_9',
        'ctpck_10',
        'ctpck_11',
        'ctpck_12',
        'cts_wheel_picked_balls',
        'cts_wheel_picked_extras',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'ctpck_id',
        'cts_id',
        'ctpck_1',
        'ctpck_2',
        'ctpck_3',
        'ctpck_4',
        'ctpck_5',
        'ctpck_6',
        'ctpck_7',
        'ctpck_8',
        'ctpck_9',
        'ctpck_10',
        'ctpck_11',
        'ctpck_12',
        'cts_wheel_picked_balls',
        'cts_wheel_picked_extras',
    ];

    public function cart_subscription() {
        return $this->belongsTo(CartSubscription::class, 'cts_id', 'cts_id');
    }

    public function getLotBallsAttribute() {
        $lot_ballss = collect([]);
        $cart_subscription = $this->cart_subscription ? $this->cart_subscription : null;
        $lottery = $cart_subscription ? $cart_subscription->lottery ? $cart_subscription->lottery : null : null;
        $draw_id = $cart_subscription ? $cart_subscription->sub_lastdraw_id : null;
        if ($lottery) {
            $lot_balls = $lottery->lot_pick_balls;
            if ($lottery->lot_id === 14 && $draw_id <= 9764 && $draw_id !== null) {
                $lot_balls = 5;
            }
            if ($lottery->lot_id === 15 && $draw_id <= 10166 && $draw_id !== null) {
                $lot_balls = 6;
            }
            for ($i = 1; $i <= $lot_balls; $i++) {
                $ball = 'ctpck_'.$i;
                $lot_ballss->push($this->$ball);
            }
        }
        return $lot_ballss;
    }

    public function getExtraBallsAttribute() {
        $extra_balls = collect([]);
        $cart_subscription = $this->cart_subscription ? $this->cart_subscription : null;
        $lottery = $cart_subscription ? $cart_subscription->lottery ? $cart_subscription->lottery : null : null;
        $draw_id = $cart_subscription ? $cart_subscription->sub_lastdraw_id : null;

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
            $ball = 'ctpck_'.$i;
            $extra_balls->push($this->$ball);
        }
        return $extra_balls;
    }


    public function getCtsWheelPickedBallsArrAttribute(){
        if(ClientService::isOrca())
            return $this->cts_wheel_picked_balls ? explode(",", $this->cts_wheel_picked_balls) : [];
        return  $this->cts_wheel_picked_balls;
    }

    public function getCtsWheelPickedExtrasArrAttribute(){
        if(ClientService::isOrca())
            return $this->cts_wheel_picked_extras ? explode(",", $this->cts_wheel_picked_extras) : [];
        return $this->cts_wheel_picked_extras;
    }
}
