<?php

namespace App\Core\Carts\Models;

use App\Core\Lotteries\Models\LiveLottery;
use App\Core\Lotteries\Models\LotteryModifier;
use App\Core\Lotteries\Models\LiveDraw;
use App\Core\Carts\Transforms\CartLiveLotterySubscriptionTransformer;
use Illuminate\Database\Eloquent\Model;

class CartLiveLotterySubscription extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'cts_id';
    protected $table = 'cart_suscriptions';
    public $timestamps = false;
    public $transformer = CartLiveLotterySubscriptionTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'crt_id',
        'lot_id',
        'cts_subExtension',
        'cts_tickets',
        'cts_price',
        'cts_ticket_extra',
        'cts_pck_type',
        'cts_ticket_byDraw',
        'cts_draws',
        'cts_ticket_nextDraw',
        'cts_winning_behaviour',
        'cts_renew',
        'cts_prc_id',
        'cts_printable_name',
        'cts_draws_by_ticket',
        'cts_day_to_play',
        'cts_next_draw_id',
        'bonus_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'cts_id',
        'crt_id',
        'lot_id',
        'cts_tickets',
        'cts_price',
        'cts_ticket_extra',
        'cts_pck_type',
        'cts_ticket_byDraw',
        'cts_draws',
        'cts_ticket_nextDraw',
        'cts_winning_behaviour',
        'cts_renew',
        'cts_prc_id',
        'cts_printable_name',
        'cts_draws_by_ticket',
        'cts_day_to_play',
        'cts_wheel',
        'cts_wheel_balls',
        'cts_wheel_lines',
        'wheel_id',
        'cts_next_draw_id',
        'bonus_id',
        'price_attributes',
        'cart_subscription_picks_attributes',
        'next_draw_attributes',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart() {
        return $this->belongsTo(Cart::class, 'crt_id', 'crt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lottery() {
        return $this->belongsTo(LiveLottery::class, 'lot_id', 'lot_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cart_subscription_pick() {
        return $this->hasOne(CartLiveLotterySubscriptionPick::class, 'cts_id', 'cts_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function draw() {
        return $this->belongsTo(LiveDraw::class, 'cts_next_draw_id', 'draw_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function modifier() {
        return $this->hasOne(LotteryModifier::class, 'modifier_id', 'cts_modifier_1');
    }

    /**
     * @return mixed|null
     */
    public function getPriceAttributesAttribute() {
        $price = $this->cts_prc_id != 0 ? $this->price : null;
        return !is_null($price) ? $price->transformer::transform($price) : $price;
    }

    /**
     * @return mixed|null
     */
    public function getCartSubscriptionPicksAttributesAttribute() {
        return $this->cart_subscription_pick ? $this->cart_subscription_pick->transformer::transform($this->cart_subscription_pick) : null;
    }

    /**
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    public function getPickTypeTextAttribute() {
        if ($this->cts_pck_type != 3) {
            return trans('lang.quick_pick');
        } else {
            return trans('lang.user_pick');
        }
    }

    /**
     * @return array
     */
    public function getPicksAttribute() {
        $pick_type = [];
        if ($this->cts_pck_type == 3) {
            $pick_type = $this->cart_subscription_pick ? $this->cart_subscription_pick->transformer ? $this->cart_subscription_pick->transformer::transform($this->cart_subscription_pick) : $this->cart_subscription_pick : null;
        }
        return $pick_type;
    }

    /**
     * @return mixed
     */
    public function getModifierAttributesAttribute() {
        return $this->modifier ? $this->modifier->transformer ? $this->modifier->transformer::transform($this->modifier) : $this->modifier : null;
    }

    /**
     * @return mixed
     */
    public function getDrawAttributesAttribute() {
        return $this->draw ? $this->draw->transformer ? $this->draw->transformer::transform($this->draw) : $this->draw : null;
    }

    public function getLotteryNameAttribute() {
        return $this->lottery ? $this->lottery->lot_name : null;
    }

    public function getDrawDateAttribute() {
        return $this->draw ? $this->draw->draw_date .' '.$this->draw->draw_time : null;
    }

    public function getDrawNumberAttribute() {
        return $this->draw ? $this->draw->draw_external_id : null;
    }
}
