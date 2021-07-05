<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Base\Traits\Utils;
use App\Core\Carts\Models\CartRaffle;
use App\Core\Carts\Models\CartRaffleSyndicate;
use App\Core\Carts\Models\CartScratchCardSubscription;
use App\Core\Carts\Models\CartSubscription;
use App\Core\Syndicates\Models\SyndicateCartSubscription;
use App\Core\Rapi\Transforms\OrderTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use Utils;

    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'crt_id';
    protected $table = 'carts';
    const CREATED_AT = 'crt_date';
    const UPDATED_AT = 'crt_lastupdate';
    public $transformer = OrderTransformer::class;

    public function payway() {
        return $this->hasOne(Payway::class, 'pay_id', 'pay_id');
    }

    public function billing() {
        $user_id = Auth::user()->usr_id;
        return $this->hasOne(Billing::class, 'crt_id', 'crt_id')
            ->where('bil_success', '=', 1)->where('usr_id', '=', $user_id);
    }

    public function cart_subscriptions() {
        return $this->hasMany(CartSubscription::class, 'crt_id', 'crt_id');
    }

    public function getDateAttribute() {
        return $this->pay_id == 246 ? $this . crt_date : $this->crt_buyDate;
    }

    public function getPaymentMethodAttribute() {
        if ($this->pay_id == 41) {
            return '#PAY_METHOD_BONUS#';
        } elseif ($this->pay_id == 246) {
            return '#PAY_METHOD_PRESALE#';
        } elseif ($this->crt_pay_method == 0) {
            return '#PAY_METHOD_ACCOUNT#';
        } elseif ($this->crt_pay_method == 2) {
            return '#PAY_METHOD_MIX# ' . $this->payway->pay_show_name;
        } else {
            $billing = $this->billing ? $this->billing->bil_ccNum_show : null;
            return $this->payway ? $billing ? $this->payway->pay_show_name . ' ' . $billing : $this->payway->pay_show_name : null;
        }
    }

    public function getOrderAmountAttribute() {
        return $this->cart_type == 2 ? $this->crt_price : $this->crt_total;
    }

    public function getStatusAttribute() {
        if ($this->crt_status == 3 || $this->crt_status == 7) {
            return '#ORDERS_DETAIL_CANCELLED#';
        } elseif ($this->crt_status == 1 && $this->pay_id == 246) {
            return '#ORDERS_DETAIL_PENDING#';
        } else {
            return '#ORDERS_DETAIL_CONFIRMED#';
        }
    }

    public function getLotteriesSubscriptionsAttribute() {
        $lotteries_subscription = collect([]);
        $this->cart_subscriptions()
            ->with(['lottery.lottery_extra_info', 'wheel', 'price', 'subscription'])
            ->get()
            ->each(function (CartSubscription $item) use ($lotteries_subscription) {
                $name = 'name_fancy_' . $this->getLanguage();
                $fancy_name = $item->lottery ? $item->lottery->lottery_extra_info ? $item->lottery->lottery_extra_info->$name : null : null;
                $wheel = $item->wheel ? $item->wheel->transformer ? $item->wheel->transformer::transform($item->wheel) : $item->wheel : null;
                $subscription_qty = $item->cts_ticket_byDraw;
                $duration = $item->duration;
                $games = $item->cts_ticket_byDraw != 0 ? ($item->cts_tickets + $item->cts_ticket_extra) / $item->cts_ticket_byDraw : 0;
                $game = [
                    'identifier' => $item->subscription ? $item->subscription->sub_id : null,
                    'cart_subscription_id' => is_null($item) ? null : $item->cts_id,
                    'lottery_identifier' => $item->lot_id,
                    'name' => $fancy_name,
                    'wheel' => $wheel,
                    'subscriptions' => $subscription_qty,
                    'duration' => $duration,
                    'games' => $games];
                $lotteries_subscription->push($game);
            });
        return $lotteries_subscription;
    }

    public function cart_raffles() {
        return $this->hasMany(CartRaffle::class, 'crt_id', 'crt_id');
    }

    public function getRafflesSubscriptionsAttribute() {
        $rafflesSubscriptions = collect([]);
        $this->cart_raffles()
            ->with(['raffle_subscription', 'raffle_draw'])
            ->get()
            ->each(function (CartRaffle $item) use ($rafflesSubscriptions) {
                $raffle_subscription = $item->raffle_subscription;
                $game = [
                    'identifier' => $item->crf_id,
                    'identifier_raffle' => $raffle_subscription ? $raffle_subscription->inf_id : null,
                    'name' => $raffle_subscription ? $raffle_subscription->raffle_name : null,
                    'tickets' => $item->tickets()['tickets'],
                    'tickets_tag' => $item->tickets()['tickets_tag'],
                ];
                $rafflesSubscriptions->push($game);
            });
        return $rafflesSubscriptions;
    }

    public function cart_raffles_syndicates() {
        return $this->hasMany(CartRaffleSyndicate::class, 'crt_id', 'crt_id');
    }

    public function getRafflesSyndicateSubscriptionsAttribute() {
        $raffle_syndicate_subscriptions = collect([]);
        $this->cart_raffles_syndicates()
            ->with(['syndicate_raffle', 'syndicate_raffle_subscription'])
            ->get()
            ->each(function (CartRaffleSyndicate $item) use ($raffle_syndicate_subscriptions) {
                $syndicate_raffle = $item->syndicate_raffle;
                $game = [
                    'identifier' => $item->cts_id,
                    'identifier_raffle_syndicate' => $syndicate_raffle->id,
                    'name' => $syndicate_raffle ? $syndicate_raffle->syndicate_raffle_name : null,
                    'subscriptions' => $item->cts_ticket_byDraw,
                    'duration' => $item->duration
                ];
                if ($syndicate_raffle && $syndicate_raffle->multi_raffle == 0) {
                    $syndicate_raffle_subscription = $item->syndicate_raffle_subscription;
                    $game['games'] = $item->games;
                    $game['free_tickets'] = $syndicate_raffle_subscription ? $syndicate_raffle_subscription->sub_ticket_extra : null;
                }
                $raffle_syndicate_subscriptions->push($game);
            });
        return $raffle_syndicate_subscriptions;
    }

    public function syndicate_cart_subscriptions() {
        return $this->hasMany(SyndicateCartSubscription::class, 'crt_id', 'crt_id');
    }

    public function getSyndicateSubscriptionsAttribute() {
        $syndicate_subscriptions = collect([]);
        $this->syndicate_cart_subscriptions()
            ->with(['syndicate.syndicate_lotteries', 'price',])
            ->get()
            ->each(function (SyndicateCartSubscription $item) use ($syndicate_subscriptions) {
                $syndicate = $item->syndicate ? $item->syndicate : null;
                $game = [
                    'identifier' => $item->cts_id,
                    'identifier_syndicate' => $syndicate ? $syndicate->id : null,
                    'name' => $syndicate ? $syndicate->printable_name : null,
                    'tag_name' => $syndicate ? '#PLAY_GROUP_NAME_' . $syndicate->name . '#' : null,
                    'tag_name_short' => $syndicate ? '#PLAY_GROUP_NAME_SHORT_' . $syndicate->name . '#' : null,
                    'subscriptions' => $item->cts_ticket_byDraw,
                    'duration' => $item->duration,
                    'games' => $item->games,
                    'free_tickets' => $item->cts_ticket_extra,
                ];
                $syndicate_subscriptions->push($game);
            });
        return $syndicate_subscriptions;
    }

    public function scratches_cart_subscriptions() {
        return $this->hasMany(CartScratchCardSubscription::class, 'crt_id', 'crt_id');
    }

    public function getScratchesSubscriptionsAttribute() {
        $scratches_subscriptions = collect([]);
        $this->scratches_cart_subscriptions()
            ->with(['scratch_card'])
            ->get()
            ->each(function (CartScratchCardSubscription $item) use ($scratches_subscriptions) {
                $scratch_card = $item->scratch_card;
                $subscription = $item->subscription;
                $id = $subscription->scratches_sub_id;
                $game = [
                    'identifier' => $item->cts_id,
                    'name' => $scratch_card ? $scratch_card->name_tag : null,
                    'games' => $item->cts_rounds,
                    'scratch_card_identifier' => $scratch_card->id,
                    'real_play' => $scratch_card ? $scratch_card->realPlayUrl($id) : null,
                ];
                $scratches_subscriptions->push($game);
            });
        return $scratches_subscriptions;
    }
    // add other products membership

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCartSubscriptionsListAttributesAttribute() {
        $relations = [
            'lottery.lotteriesBoostedJackpot.lotteriesModifier',
            'wheel',
            'price.price_lines',
            'next_draw.lottery',
            'next_draw.raffles',
            'cart_subscription_pick.cart_subscription.lottery',
        ];
        $cartSubscriptions = $this->cart_subscriptions()
            ->with($relations)
            ->get();

        return SetTransformToModelOrCollectionService::execute($cartSubscriptions);
    }
}
