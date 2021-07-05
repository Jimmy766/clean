<?php

namespace App\Core\Carts\Models;

use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Base\Traits\Pixels;
use App\Core\Syndicates\Models\SyndicateCartSubscription;
use App\Core\Users\Models\UsersReferringCode;
use App\Core\Memberships\Models\MembershipCartSubscription;
use App\Core\Rapi\Models\Payway;
use App\Core\Rapi\Models\Promotion;
use App\Core\Rapi\Models\Site;
use App\Core\Carts\Transforms\CartTransformer;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use Pixels;

    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'crt_id';
    const CREATED_AT = 'crt_date';
    const UPDATED_AT = 'crt_lastupdate';
    public $transformer = CartTransformer::class;
    public $promotion_discount_value = null;
    public $promotion_high_value = null;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'crt_date',
        'usr_id',
        'crt_price',
        'crt_currency',
        'crt_email',
        'pay_id',
        'crt_ip',
        'cart_type',
        'crt_total',
        'crt_discount',
        'crt_from_account',
        'crt_promotion_code',
        'crt_promotion_points',
        'crt_pay_method',
        'site_id',
        'crt_lastupdate',
        'crt_affcookie',
        'crt_track',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_content',
        'utm_term',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'crt_id',
        'crt_date',
        'usr_id',
        'crt_price',
        'crt_currency',
        'crt_email',
        'pay_id',
        'crt_status',
        'crt_ip',
        'cart_type',
        'crt_total',
        'crt_discount',
        'crt_from_account',
        'crt_promotion_code',
        'crt_promotion_points',
        'crt_pay_method',
        'site_id',
        'crt_lastupdate',
        'crt_affcookie',
        'crt_track',
        'cart_subscriptions_list_attributes',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_content',
        'utm_term',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cart_subscriptions() {
        return $this->hasMany(CartSubscription::class, 'crt_id', 'crt_id')
            ->whereHas('lottery', function ($query) {
                $query->where('lot_live', '=', 0);
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cart_live_subscriptions() {
        return $this->hasMany(CartLiveLotterySubscription::class, 'crt_id', 'crt_id')
            ->whereHas('lottery', function ($query) {
                $query->where('lot_live', '=', 1);
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syndicate_cart_subscriptions() {
        return $this->hasMany(SyndicateCartSubscription::class, 'crt_id', 'crt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cart_raffles() {
        return $this->hasMany(CartRaffle::class, 'crt_id', 'crt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syndicate_cart_raffles() {
        return $this->hasMany(CartRaffleSyndicate::class, 'crt_id', 'crt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function membership_cart_subscriptions() {
        return $this->hasMany(MembershipCartSubscription::class, 'crt_id', 'crt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scratches_cart_subscriptions() {
        return $this->hasMany(CartScratchCardSubscription::class, 'crt_id', 'crt_id');
    }

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

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getSyndicateCartSubscriptionsListAttributesAttribute() {
        $cart_syndicate_subscriptions = collect([]);
        $this->syndicate_cart_subscriptions()
            ->with(['syndicate', 'price.syndicate_price_lines', 'price.syndicate.syndicate_lotteries', 'price.lottery_time_draws'])
            ->get()
            ->each(function ($item) use ($cart_syndicate_subscriptions) {
            $cart_syndicate_subscriptions->push($item->transformer::transform($item));
        });
        return $cart_syndicate_subscriptions;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCartRafflesListAttributesAttribute() {
        $cart_raffles = collect([]);
        $relations = [
            'price.price_lines',
            'raffleFromCartRaffles.raffle_prices',
        ];
        $this->cart_raffles()
            ->with($relations)
            ->each(function ($item) use ($cart_raffles) {
            $cart_raffles->push($item->transformer::transform($item));
        });
        return $cart_raffles;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCartRafflesSyndicateListAttributesAttribute() {
        $syndicate_cart_raffles = collect([]);
        $this->syndicate_cart_raffles()
            ->with(['price.price_lines'])
            ->get()
            ->each(function ($item) use ($syndicate_cart_raffles) {
            $syndicate_cart_raffles->push($item->transformer::transform($item));
        });
        return $syndicate_cart_raffles;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCartLiveSubscriptionsListAttributesAttribute() {
        $cart_live_subscriptions = collect([]);
        $this->cart_live_subscriptions()
            ->with(['lottery', 'draw', 'cart_subscription_pick.cart_subscription.lottery', 'modifier'])
            ->get()
            ->each(function ($item) use ($cart_live_subscriptions) {
                $cart_live_subscriptions->push($item->transformer::transform($item));
        });
        return $cart_live_subscriptions;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCartScratchCardSubscriptionsListAttributesAttribute() {
        $cart_scratch_card_subscriptions = collect([]);
        $this->scratches_cart_subscriptions()
            ->with(['price.prices_lines'])
            ->get()
            ->each(function ($item) use ($cart_scratch_card_subscriptions) {
            $cart_scratch_card_subscriptions->push($item->transformer ? $item->transformer::transform($item) : $item);
        });
        return $cart_scratch_card_subscriptions;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getCartMembershipSubscriptionsListAttributesAttribute() {
        $membership_cart_subscriptions = collect([]);
        $this->membership_cart_subscriptions()
            ->get()
            ->each(function ($item) use ($membership_cart_subscriptions) {
                $membership_cart_subscriptions->push($item->transformer ? $item->transformer::transform($item) : $item);
            });
        return $membership_cart_subscriptions;
    }

    public function site() {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    public function payway() {
        return $this->belongsTo(Payway::class, 'pay_id', 'pay_id');
    }

    public function getPaywayAttributesAttribute() {
        return $this->payway();
    }

    public function getPromotionAttributesAttribute() {
        $promotion = Promotion::where('code', '=', $this->crt_promotion_code)
            ->where('sys_id', '=', request('client_sys_id'))
            ->first();
        return $promotion ? $promotion->transformer ? $promotion->transformer::transform($promotion) : $promotion : null;
    }

    public function apply_promo($code) {
        $this->crt_promotion_code = "";
        $this->crt_promotion_points = 0;


        if ($this->cart_type == 4)
            return false;
        $system_id = request('client_sys_id');
        $promotions =  Promotion::where('code', '=', $code)
            ->where('start_date', '<=', now())
            ->where('expiration_date', '>', now())
            ->where('sys_id', '=', $system_id);
        $promotion = $promotions ? $promotions->first() : null;
        if (!$promotion)
            return false;
        // Total de usos, que no estén cancelados
        if ($promotion->promo_max_uses != 0 && $promotion->promo_max_uses <= $promotion->promotion_usages->count())
            return false;
        // Usos del usuario
        $user_id = request('user_id');
        if ($user_id) {
            $user_uses = $promotion->promotion_usages->where('usr_id', $user_id)->where("status", "<>", 3)->count();
            if ($promotion->max_uses != 0 && $user_uses >= $promotion->max_uses ){
                return false;
            }
        }
        $this->promotion_discount_value = 0;
        $this->promotion_high_value = 0;
        switch ($promotion->discount_type) {
            /**
             * Si promo_product != 0,1 no aplica
             * Si promo_product == 0,1 y tiene membresia no aplica
             **/
            case 7:
                if (!$user_id) {
                    $discount_level = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->sortBy('high_value')->first(): null;
                    $discount_value = $discount_level ? $discount_level->discount_value : null;
                    $this->promotion_discount_value = $discount_value;
                } else {
                    return false;
                }
                break;
            /**
             * Si promo_product != 0,1 -- calcula amount
             * Si promo_product == 0,1 y tiene membresia -- calcula amount sin membresia
             **/
            case 6:
                // Si es nuevo usuario y tiene compras
                if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
                    return false;
                }
                $amount = $this->crt_total;
                // Si no aplica a todos || aplica a todos y tiene membresias calculo amount del cart, sino es crt_total
                if (($promotion->promo_product != '1' && $promotion->promo_product != '0') ||
                    ($promotion->promo_product == '1' || $promotion->promo_product == '0') && $this->membership_cart_subscriptions->isNotEmpty()) {
                    $amount = $this->amount_promo_cart($promotion);
                }
                if ($amount > 0) {
                    // Si amount > 0 busco high_value y discount_value mayor que amount
                    $discount_levels = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)->sortBy('high_value') : null;
                    // Si no selecciono el mayor disponible
                    $discount_level = $discount_levels ? $discount_levels->first() : $promotion->promotion_discount_levels->sortByDesc('high_value')->first();
                    if ($discount_level) {
                        $this->promotion_discount_value = $discount_level->discount_value;
                        $this->promotion_high_value = $discount_level->high_value;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

                break;
            /**
             * No tienen en cuenta promo_product
             **/
            case 5:
                // Si es nuevo usuario y tiene compras
                if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
                    return false;
                }
                $discount_level = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->sortBy('high_value')->first() : null;
                if ($discount_level) {
                    $this->promotion_discount_value = $discount_level->discount_value;
                } else {
                    return false;
                }
                break;
            /**
             * Si promo_product != 0,1 -- calcula amount con membresia
             * Si promo_product == 0,1 y tiene membresia -- calcula amount sin membresia
             * Si promo_product == 0,1 y no tiene membresia
             **/
            case 4:
                if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
                    return false;
                }
                $amount = $this->crt_total;
                if (($promotion->promo_product != '1' && $promotion->promo_product != '0') ||
                    ($promotion->promo_product == '1' || $promotion->promo_product == '0') && $this->membership_cart_subscriptions->isNotEmpty()) {
                    $amount = $this->amount_promo_cart($promotion);
                }
                if ($amount > 0) {
                    $discount_level = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)->sortBy('high_value')->first() : null;
                    if ($discount_level) {
                        $this->promotion_discount_value = $discount_level->discount_value;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
                break;
            /**
             * Si promo_product != 0,1 -- calcula amount con membresia
             * Si promo_product == 0,1 y tiene membresia -- calcula amount con membresia
             * Si promo_product == 0,1 y no tiene membresia -- no calcula nada
             **/
            case 3:
                if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
                    return false;
                }
                if ($user_id) {
                    $user_referring_code = UsersReferringCode::find($user_id);
                    if ($user_referring_code && $user_referring_code->usr_referring_code === $code) {
                        return false;
                    }
                }


                if (($promotion->promo_product == '1' || $promotion->promo_product == '0') && $this->membership_cart_subscriptions->isEmpty()) {
                    $discount_level = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->sortBy('high_value')->first() : null;
                }
                // Si no aplica a todos calculo con membresias || aplica a todos y tiene membresias calculo amount sin membresias
                if (($promotion->promo_product != '1' && $promotion->promo_product != '0') ||
                    ($promotion->promo_product == '1' || $promotion->promo_product == '0') && $this->membership_cart_subscriptions->isNotEmpty()) {
                    $amount = $this->amount_promo_cart($promotion);
                    $discount_level = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)->sortBy('high_value')->first() : null;
                }
                if ($discount_level) {
                    $crt_discount = $discount_level->discount_value;
                    if ($crt_discount > 0) {
                        $this->crt_price = round($this->crt_total - $crt_discount, 2);;
                        $this->crt_discount = $crt_discount;
                        $this->promotion_discount_value = $crt_discount;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }

                break;
            /**
             * Si promo_product != 0,1 -- calcula amount con membresias
             * Si promo_product == 0,1 -- no calcula nada
             **/
            case 2:
                // Si es nuevo usuario y tiene compras
                if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
                    return false;
                }
                if ($promotion->promo_product == '1' || $promotion->promo_product == '0') {
                    $discount_levels = $promotion->promotion_discount_levels->where('high_value', '>', $this->crt_total)->sortBy('high_value');
                    if ($discount_levels->isEmpty()) {
                        return false;
                    }
                    $discount_level = $discount_levels->first();
                    $crt_discount = round(($discount_level->discount_value * $this->crt_total)/100, 2);
                    $crt_price = round($this->crt_total - $crt_discount, 2);
                    if ($crt_discount > 0) {
                        $this->crt_discount = $crt_discount;
                        $this->crt_price = $crt_price;
                        $this->promotion_discount_value = $discount_level->discount_value;
                    } else {
                        return false;
                    }
                } else {
                    $crt_discount = 0;
                    $discount_value = 0;
                    // calculo amount con membresias
                    $amount = $this->amount_promo_cart($promotion);
                    if ($amount > 0) {
                        $discount_level = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->where('high_value', '>', $amount)->sortBy('high_value')->first() : null;
                        if ($discount_level) {
                            $discount_value = $discount_level->discount_value;
                            $promo_discount = round(($discount_value * $amount) / 100,2);
                            $crt_discount = round($promo_discount, 2);
                        }
                    }
                    if ($crt_discount > 0) {
                        $crt_price = round($this->crt_total - $crt_discount, 2);
                        $this->crt_price = $crt_price;
                        $this->crt_discount = $crt_discount;
                        $this->promotion_discount_value = $discount_value;
                    } else {
                        return false;
                    }
                }
                break;
            /**
             * Calcula tickets extra por loterias
             **/
            case 1:
                if ($promotion->user_type == 2 && $user_id && $this->user_purchases()) {
                    return false;
                }
                if ($user_id) {
                    $user_referring_code = UsersReferringCode::find($user_id);
                    if ($user_referring_code->usr_referring_code === $code) {
                        return false;
                    }
                }
                $tickets_extra = 0;
                // si aplica a todos los productos o aplica a todas las loterias obtengo suscripciones
                if ($promotion->promo_product_lot_id == '0' || (($promotion->promo_product == '1' || $promotion->promo_product == '0') && $this->membership_cart_subscriptions->isEmpty())) {
                    $cart_subscriptions = $this->cart_subscriptions ? $this->cart_subscriptions->where('lot_live', 0) : null;
                } else {
                    // obtengo suscripciones del cart a las que aplica
                    $cart_subscriptions = $this->cart_subscriptions ? $this->cart_subscriptions->whereIn('lot_id', $promotion->lotteries()) : null;
                }
                if ($cart_subscriptions && $cart_subscriptions->isNotEmpty()) {
                    foreach ($cart_subscriptions as $cart_subscription) {
                        // obtengo tickets por loterias segun precio de cada subscripcion
                        $discount_levels = $promotion->promotion_discount_levels ? $promotion->promotion_discount_levels->where('high_value', '>', $cart_subscription->cts_price)->sortBy('high_value') : null;
                        $discount_level = $discount_levels ? $discount_levels->first() : null;
                        if ($discount_level) {
                            $cart_subscription->cts_ticket_extra = $discount_levels->discount_value;
                            $cart_subscription->save();
                            $tickets_extra += $discount_levels->discount_value;
                        }
                    }
                }
                if ($tickets_extra > 0) {
                    $this->promotion_discount_value = $tickets_extra;
                } else {
                    return false;
                }
        }
        $this->crt_promotion_code = $code;
        return true;
    }

    public function amount_promo_cart($promotion) {
        $amount = 0;
        if($promotion->promo_product != '1' and $promotion->promo_product != '0') {
            if ($this->cart_subscriptions->isNotEmpty()) {
                if ($promotion->lotteries()->isNotEmpty()) {
                    foreach ($this->cart_subscriptions as $cart_subscription) {
                        if ($promotion->lotteries()->first() === 0 || $promotion->lotteries()->contains($cart_subscription->lot_id)) {
                            $amount += $cart_subscription->cts_price;
                        }
                    }
                }
            }
            if ($this->syndicate_cart_subscriptions->isNotEmpty()){
                if ($promotion->syndicates()->isNotEmpty()) {
                    foreach ($this->syndicate_cart_subscriptions as $syndicate_cart_subscription) {
                        if ($promotion->syndicates()->first() === 0 || $promotion->syndicates()->contains($syndicate_cart_subscription->syndicate_id)) {
                            $amount += $syndicate_cart_subscription->cts_price;
                        }
                    }
                }
            }
            if ($this->cart_raffles->isNotEmpty()) {
                if ($promotion->raffles()->isNotEmpty()) {
                    foreach ($this->cart_raffles as $cart_raffle) {
                        if ($promotion->raffles()->first() === 0 || $promotion->raffles()->contains($cart_raffle->rff_id)) {
                            $amount += $cart_raffle->crf_price;
                        }
                    }
                }
            }
            if ($this->syndicate_cart_raffles->isNotEmpty()) {
                if ($promotion->raffle_syndicates()->isNotEmpty()) {
                    foreach ($this->syndicate_cart_raffles as $syndicate_cart_raffle) {
                        if ($promotion->raffle_syndicates()->first() === 0 || $promotion->raffle_syndicates()->contains($syndicate_cart_raffle->rsyndicate_id)) {
                            $amount += $syndicate_cart_raffle->cts_price;
                        }
                    }
                }
            }
            if ($this->membership_cart_subscriptions->isNotEmpty() && $promotion->promo_product != '1' && $promotion->promo_product != '0') {
                if ($promotion->memberships()->isNotEmpty()) {
                    foreach ($this->membership_cart_subscriptions as $membership_cart_subscription) {
                        if ($promotion->memberships()->first() === 0 || $promotion->memberships()->contains($membership_cart_subscription->memberships_id)) {
                            $amount += $membership_cart_subscription->cts_price;
                        }
                    }
                }
            }
            if ($this->scratches_cart_subscriptions->isNotEmpty()) {
                if ($promotion->scratches()->isNotEmpty()) {
                    foreach ($this->scratches_cart_subscriptions as $scratches_cart_subscription) {
                        if ($promotion->scratches()->first() === 0 || $promotion->scratches()->contains($scratches_cart_subscription->scratches_id)) {
                            $amount += $scratches_cart_subscription->cts_price;
                        }
                    }
                }
            }
        }
        return $amount;
    }

    public function user_purchases() {
        return Cart::where('crt_status', '=', 2)
            ->where('usr_id', '=', request()['user_id'])
            ->where('pay_id', '!=', 23)
            ->get()
            ->count() > 0;
    }

    public function reset_promocode() {
        $this->crt_promotion_code = '';
        $this->crt_promotion_points = 0;
        $this->crt_to_account = 0;
        $this->crt_discount = 0;
        $this->promotion_discount_value = 0;
        $this->crt_price = $this->crt_total;
        $this->save();
        $subscriptions = $this->cart_subscriptions;
        $subscriptions->each(function (CartSubscription $item) {
            $item->cts_ticket_extra = 0;
            $item->save();
        });
    }

    public function cart_with_disable_products(){
        $cart_disable = CartDisableProds::from($this);

        return $cart_disable->hasBlockedProducts() ? null : $cart_disable;
    }
}
