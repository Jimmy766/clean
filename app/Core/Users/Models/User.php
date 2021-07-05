<?php

namespace App\Core\Users\Models;

use App\Core\Base\Services\ClientService;
use App\Core\Base\Services\FastTrackLogService;
use App\Core\Base\Services\SendLogConsoleService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Base\Traits\LogCache;
use App\Core\Base\Traits\Utils;
use App\Core\Carts\Models\Cart;
use App\Core\Casino\Models\CasinoBonusUser;
use App\Core\Casino\Models\CasinoGamesTransaction;
use App\Core\Lotteries\Models\LiveLottery;
use App\Core\Lotteries\Models\LiveLotterySubscription;
use App\Core\Lotteries\Models\Lottery;
use App\Core\Lotteries\Models\LotterySubscription;
use App\Core\Raffles\Models\RaffleSubscription;
use App\Core\Rapi\Models\Billing;
use App\Core\ScratchCards\Models\ScratchCardSubscription;
use App\Core\Syndicates\Models\SyndicatePrize;
use App\Core\Syndicates\Models\SyndicateRafflePrize;
use App\Core\Countries\Models\Country;
use App\Core\Memberships\Models\Membership;
use App\Core\Memberships\Models\MembershipAssignPool;
use App\Core\Rapi\Models\Site;
use App\Core\Rapi\Models\State;
use App\Core\Rapi\Models\Transaction;
use App\Core\Memberships\Transforms\MembershipUserTransformer;
use App\Core\Users\Transforms\UserTransformer;
use App\Core\Users\Transforms\UserWinningsLotteryListTransformer;
use App\Core\Users\Transforms\UserWinningsRaffleListTransformer;
use App\Core\Users\Transforms\UserWinningsScratchCardListTransformer;
use App\Core\Users\Transforms\UserWinningsSyndicateListTransformer;
use App\Core\Users\Transforms\UserWinningsSyndicateRaffleListTransformer;
use App\Core\Users\Models\UserLogin;
use App\Core\Users\Models\UsersReferringCode;
use App\Core\Users\Models\UserTitle;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\HasApiTokens;


// faltan poner los optin
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use LogCache;
    use Utils, CartUtils;
    public $transformer = UserTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'usr_name',
        'usr_lastname',
        'usr_email',
        'usr_password',
        'country_id',
        'usr_state',
        'usr_address1',
        'usr_address2',
        'usr_city',
        'usr_zipcode',
        'usr_phone',
        'usr_mobile',
        'usr_ssn',
        'usr_ssn_type',
        'usr_language',
        'usr_altEmail',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_content',
        'utm_term',
        'usr_cookies',
        'usr_track',
        'usr_cookies_data4',
        'usr_cookies_data5',
        'usr_cookies_data6',
        'sys_id',
        'site_id',
        'usr_active',
        'usr_aid',
        'usr_regdate',
        'usr_lastUpdate',
        'usr_NoPromoemails',
        'usr_NoPromoemails_date',
        'usr_notTelemCall',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'usr_name',
        'usr_lastname',
        'usr_email',
        'usr_phone',
        'usr_mobile',
        'usr_address1',
        'usr_address2',
        'usr_city',
        'usr_zipcode',
        'usr_ssn',
        'usr_ssn_type',
        'usr_language',
        'usr_altEmail',
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_content',
        'utm_term',
        'state_attributes',
        'site_attributes',
    ];

    public $connection = 'mysql_external';
    protected $primaryKey = 'usr_id';
    const CREATED_AT = 'usr_regdate';
    const UPDATED_AT = 'usr_lastupdate';
    /**
     * PERIOD USERS HOURS
     */
    const PERIOD_USER = 48;


    public $casino_bonus_amount;

    /* Attribute for password forgot token creation
    *  Table: password_resets
    */
    public function getEmailAttribute() {
        return $this->usr_email;
    }

    /* Needed to change default email field in passport login */

    public function findForPassport($username, $password = null) {
        $client_id = request('client_id') ? request('client_id') :
            explode(':', base64_decode(explode(' ', request()->header('Authorization'))[1]))[0];
        $cache = 'client_' . $client_id;

        $cache .= request()->has("sys_id") ? request()->get("sys_id") : '';

        $client = ClientService::getClient($client_id);

        $client_site = $this->rememberCache('client_site_' . $client->id, Config::get('constants.cache_daily'), function () use ($client) {
            $site = $client->site;
            return $site ? $site : null;
        });
        $password = $password ?? request('password');
        $user = $this->where('usr_email', $username)
            ->where('sys_id', $client_site->sys_id)
            ->where('usr_password', $password)
            ->where('usr_active', 1)->first();
        $client_domain = $client_site ? $client_site->site_url_https : 'https://www.wintrillions.com';
        request()->merge(['user_id'=> isset($user->usr_id) ? $user->usr_id : null]);
        request()->merge(['client_domain' => $client_domain]);
        return $user;
    }

    public function validateForPassportPasswordGrant($password) {
        $sendLogConsoleService = new SendLogConsoleService();
        try {
            $codeResponseFastTrack = FastTrackLogService::loginUser();
            if (($codeResponseFastTrack != 200) && (config('app.env') != 'stage')) {
                $sendLogConsoleService->execute(
                    request(),
                    'error',
                    'access',
                    'FT API Login Error - Wrong Code',
                    ''
                );
            }
        } catch (\Exception $exception) {
            $sendLogConsoleService->execute(
                request(),
                'error',
                'access',
                'FT API Error - Login: ' . $exception->getMessage(),
                ''
            );
        }

        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function site() {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country() {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state() {
        return $this->belongsTo(State::class, 'usr_state', 'state_id');
    }

    function getSiteAttributesAttribute() {
        return $this->rememberCache('user_site_' . $this->usr_id, Config::get('constants.cache_daily'), function () {
            $site = $this->site;
            return $site ? $site->transformer::transform($site) : null;
        });
    }

    public function getStateAttributesAttribute() {
        return $this->rememberCache('user_state_' . $this->usr_id, Config::get('constants.cache_daily'), function () {
            $state = $this->state;
            if ($state) {
                return $state->transformer::transform($state);
            }
            return ['name' => $this->usr_state, 'country' => $this->country_attributes];
        });
    }


    public function getCountryAttributesAttribute() {
        if($this->country != NULL){
            return $this->rememberCache('user_country_' . $this->usr_id, Config::get('constants.cache_daily'), function () {
                $country = $this->country;
                return $country->transformer::transform($country);
            });
        }else{
        return "";
        }


    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carts() {
        return $this->hasMany(Cart::class, 'usr_id', 'usr_id');
    }

    public function last_cart() {
        return $this->hasOne(Cart::class, 'usr_id', 'usr_id')
            ->where('crt_currency', '=', request('country_currency'))
            ->where('cart_type', '=', 1)
            ->where('crt_status', '=', 0)
            ->where("crt_lastStep", "<>", 4) // luego da cart not allowed en el get cart
            ->where("crt_done", "=", 0)
            ->whereRaw("crt_date > now() - interval 48 hour")
            ->orderByDesc('crt_date');
    }

    public function getLastCartAttributesAttribute() {
        $last_cart = $this->last_cart;
        return $last_cart ? $last_cart->cart_with_disable_products() : null;
    }

    public function last_cart_telem() {
        return $this->hasOne(Cart::class, 'usr_id', 'usr_id')
            ->leftJoin('telem_calls as tc', function($join){
                $join->on('tc.crt_id', '=', 'carts.crt_id');
            })
            ->where('carts.crt_currency', '=', request('country_currency'))
            ->where('carts.cart_type', '=', 1)
            ->where('carts.crt_status', '=', 0)
            ->where("carts.crt_lastStep", "<>", 4) // luego da cart not allowed en el get cart
            ->where("carts.crt_done", "=", 0)
            ->where(
                function ($query) {
                    $query->where('carts.crt_host', '=', 'admin telem')
                        ->orWhereNotNull('tc.crt_id');
                }
            )->select(['carts.*'])
            ->whereRaw("crt_date > now() - interval 1 WEEK")
            ->orderByDesc('crt_date');
    }

    public function getLastCartTelemAttributesAttribute() {
        $last_cart = $this->last_cart_telem;
        return $last_cart ? $last_cart->cart_with_disable_products() : null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions() {
        return $this->hasMany(LotterySubscription::class, 'usr_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function raffle_subscriptions() {
        return $this->hasMany(RaffleSubscription::class, 'usr_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function live_subscriptions() {
        return $this->hasMany(LiveLotterySubscription::class, 'usr_id', 'usr_id')
            ->whereHas('ticket')
            ->whereHas('cart_subscription')
            ->whereHas('draw');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syndicate_prize() {
        return $this->hasMany(SyndicatePrize::class, 'usr_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function syndicate_raffle_prize() {
        return $this->hasMany(SyndicateRafflePrize::class, 'usr_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scratch_card_subscriptions() {
        return $this->hasMany(ScratchCardSubscription::class, 'usr_id', 'usr_id');
    }

    public function getSubscriptionsListAttribute() {
        return $this->subscriptions->whereIn('lot_id', Lottery::where('lot_live', '=', 0)->pluck('lot_id'));
    }

    public function getSubscriptionLiveListAttribute() {
        return $this->live_subscriptions()
            ->whereIn('lot_id', LiveLottery::where('lot_live', '=', 1)->pluck('lot_id'))
            ->with(['lottery.modifiers', 'modifier', 'cart_subscription', 'subscription_picks.subscription.lottery', 'draw.lottery', 'ticket.subscription.modifier', 'ticket.subscription.lottery'])
            ->get();
    }

    public function getSubscriptionScratchCardListAttribute() {
        return $this->scratch_card_subscriptions;
    }

    public function getTransactionsListAttribute() {
        return Transaction::all()->sortBy('type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function referring_code() {
        return $this->hasOne(UsersReferringCode::class, 'usr_id', 'usr_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user_title() {
        return $this->belongsTo(UserTitle::class, 'usr_title', 'id');
    }

    public function getTitleAttribute() {
        return $this->rememberCache('user_title_' . $this->usr_id, Config::get('constants.cache_daily'), function () {
            $user_title = $this->user_title;
            return $user_title ? $user_title->transformer ? $user_title->transformer::transform($user_title) : $user_title : null;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function casino_bonus_user() {
        return $this->hasMany(CasinoBonusUser::class, 'usr_id', 'usr_id')
            ->where('status', '=', 1)
            ->where('expiration_date', '>=', date('Y-m-d'));
    }

    public function casino_bonus_amount() {
        $amount = 0;
        $this->casino_bonus_user->each(function ($item, $key) use (&$amount) {
            $amount += $item->amount_converted;
        });
        $this->casino_bonus_amount = $amount;
        return $amount;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function membership() {
        return $this->hasOne(Membership::class, 'id', 'usr_membership_level')
            ->where('id', '!=', 0);
    }

    public function getUserMembershipAttribute() {
        $membership = $this->membership;
        if ($membership) {
            $membership->transformer = MembershipUserTransformer::class;
            return $membership->transformer::transform($membership);
        }
        return $membership;
    }

    /**
     * @return int
     */
    public function getTotalWinningsAttribute() {
        return 1;
    }

    public function getTotalBalanceAttribute() {
        return $this->usr_acumulado + $this->usr_vip_bonus + $this->casino_bonus_amount;
    }

    public function getTotalBalanceClientCurrencyAttribute() {
        $total_balance = $this->usr_acumulado + $this->usr_vip_bonus + $this->casino_bonus_amount;
        if ($this->curr_code != request('country_currency')) {
            $factor = $this->convertCurrency($this->curr_code, request('country_currency'));
            return round($factor * $total_balance, 2);
        }
        return $total_balance;
    }

    public function billings() {
        $last6 = new \DateTime();
        $last6->sub(new \DateInterval('P6M'));
        $now = new \DateTime();
        return $this->hasMany(Billing::class, 'usr_id', 'usr_id')
            ->whereHas('credit_card')
            ->whereHas('cart', function ($query) use ($last6) {
                $query->where('usr_id', '=', $this->usr_id);
                $query->where(function ($query) {
                    $query->where('crt_processor_3d', '=', null)
                        ->orWhere('crt_processor_3d', '=', '');
                });
            })
            ->where('bil_success', '=', 1)
            ->where(function ($query) use ($now) {
                $query->where('bil_ccExpYear', '>', $now->format('Y'))
                    ->orWhere([
                        ['bil_ccExpYear', '=', $now->format('Y')],
                        ['bil_ccExpMonth', '>=', $now->format('n')]
                    ]);
            });
    }

    public function getQuickDepositAttribute() {
        $billing = $this->billings()->orderByDesc('bil_date')->limit(1)->get();
        return $billing->isNotEmpty() ? $billing->first()->crt_id : 0;
    }


    public function winnings() {
        $list = collect([]);

        //Lotteries
        $lotteries = $this->subscriptions()
            ->with('tickets_winnings.subscription.lottery', 'tickets_winnings.draw', 'tickets_winnings.draw.live_lottery')
            ->has('tickets_winnings.draw')
            ->get();
        $lotteries->each(function ($itemLottery, $key) use ($list) {
            $itemLottery->tickets_winnings->each(function ($itemTicket, $key) use ($list, $itemLottery) {
                $itemTicket->transformer = UserWinningsLotteryListTransformer::class;
                $list->push($itemTicket->transformer ? $itemTicket->transformer::transform($itemTicket) : $itemTicket);
            });
        });

        //Raffles
        $raffles = $this->raffle_subscriptions()
            ->with('tickets_winnings.raffle_draw', 'raffle')
            ->has('tickets_winnings');
        $raffles->each(function ($itemRaffle, $key) use ($list) {
            $itemRaffle->transformer = UserWinningsRaffleListTransformer::class;
            $list->push($itemRaffle->transformer ? $itemRaffle->transformer::transform($itemRaffle) : $itemRaffle);
        });

        //Syndicates
        $syndicates = $this->syndicate_prize()
            ->with('syndicate_subscription.syndicate', 'syndicate_subscription.syndicate_lottery.region', 'ticket.draw')
            ->get();
        $syndicates->each(function ($itemSyndicates, $key) use ($list) {
            $itemSyndicates->transformer = UserWinningsSyndicateListTransformer::class;
            $list->push($itemSyndicates->transformer ? $itemSyndicates->transformer::transform($itemSyndicates) : $itemSyndicates);
        });

        //Syndicates Raffles
        $syndicates_raffles = $this->syndicate_raffle_prize()
            ->with('syndicate_raffle_subscriptions.raffle_syndicate', 'raffle_ticket.raffle_draw')
            ->get();
        $syndicates_raffles->each(function ($itemSyndicateRaffle, $key) use ($list) {
            $itemSyndicateRaffle->transformer = UserWinningsSyndicateRaffleListTransformer::class;
            $list->push($itemSyndicateRaffle->transformer ? $itemSyndicateRaffle->transformer::transform($itemSyndicateRaffle) : $itemSyndicateRaffle);
        });

        //Scratches
        $scratches = $this->scratch_card_subscriptions()
            ->with('movements', 'scratch_card')
            ->get()
            ->filter(function ($value, $key) {
                return $value->prize > 0;
            });
        $scratches->each(function ($itemScratch, $key) use ($list) {
            $itemScratch->transformer = UserWinningsScratchCardListTransformer::class;
            $list->push($itemScratch->transformer ? $itemScratch->transformer::transform($itemScratch) : $itemScratch);
        });
        return $list->isNotEmpty() ? $list->sortByDesc('draw_date')->values() : $list;
    }


    public function winnings_pending() {
        $list = collect([]);

        //Lotteries
        $lotteries = $this->subscriptions()
            ->with('tickets_winnings_pending.subscription.lottery', 'tickets_winnings_pending.draw', 'tickets_winnings_pending.draw.live_lottery')
            ->has('tickets_winnings_pending.draw')
            ->get();
        $lotteries->each(function ($itemLottery, $key) use ($list) {
            $itemLottery->tickets_winnings_pending->each(function ($itemTicket, $key) use ($list, $itemLottery) {
                $itemTicket->transformer = UserWinningsLotteryListTransformer::class;
                $list->push($itemTicket->transformer ? $itemTicket->transformer::transform($itemTicket) : $itemTicket);
            });
        });
        return $list->isNotEmpty() ? $list->sortByDesc('draw_date')->values() : $list;
    }

    public function getUserPointCashAttribute() {
        return $this->trade_points($this->usr_points);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function membership_assign_pool() {
        return $this->hasOne(MembershipAssignPool::class, 'usr_id', 'usr_id')
            ->where('membership_id','=',$this->usr_membership_level);
    }

    public function membership_benefits() {
        $membership_assign_pool = $this->membership_assign_pool;
        return $membership_assign_pool ? $this->membership_assign_pool->pcode_benefits : collect([]);
    }

    public function logins() {
        return $this->hasMany(UserLogin::class, 'usr_id', 'usr_id');
    }


    public function hasPlayedRedTiger(){
        $red = CasinoGamesTransaction::where("casino_provider_id", "=", 3)
            ->where("usr_id", "=", $this->usr_id)
            ->first();

        if($red){
            return true;
        }

        return false;
    }
}
