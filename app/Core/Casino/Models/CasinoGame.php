<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Models\CasinoGamesBetConfig;
use App\Core\Casino\Models\CasinoGamesCategory;
use App\Core\Casino\Models\CasinoGamesDescription;
use App\Core\Casino\Models\CasinoGamesToken;
use App\Core\Casino\Models\CasinoGamesTransaction;
use App\Core\Casino\Models\CasinoProvider;
use App\Core\Clients\Models\Client;
use App\Core\Base\Models\CoreModel;
use App\Core\Base\Services\LocationResolveOtherLangService;
use App\Core\Casino\Services\GenerateUrlDemoCasinoGameService;
use App\Core\Casino\Services\GenerateUrlRealPlayCasinoGameService;
use App\Core\Users\Services\GetBalanceUserService;
use App\Core\Base\Services\GetOriginRequestService;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Casino\Transforms\CasinoGameTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class CasinoGame extends CoreModel
{
    use ApiResponser;
    protected $guarded = [];
    public $connection = 'mysql_external';
    public $timestamps = false;
    public $transformer = CasinoGameTransformer::class;
    public const TAG_CACHE_MODEL = 'TAG_CACHE_CASINO_GAME_';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'external_casino_id',
        'game_code',
        'game_code_mobile',
        'game_rtp',
        'game_new',
        'game_hot',
        'lines',
        'multiplier',
        'game_blocked',
        'is_flash',
        'is_lobby',
        'live',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'name',
        'casino_provider_id',
        'external_casino_id',
        'game_code',
        'game_code_mobile',
        'game_rtp',
        'game_new',
        'game_hot',
        'lines',
        'multiplier',
        'game_blocked',
        'is_flash',
        'live',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider() {
        return $this->belongsTo(CasinoProvider::class, 'casino_provider_id', 'id')->where('active', '=', '1');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function description() {
        $sys_id = Cache::remember('sys_id_client_id'.request()['oauth_client_id'],
            60, function () {
                return Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id;
            });

        $lang = LocationResolveOtherLangService::execute();

        return $this->hasMany(CasinoGamesDescription::class, 'casino_game_id', 'id')->where([
            'active' => 1,
            'sys_id' => $sys_id,
            'lang' => $lang
        ]);
    }

    public function first_description() {
        $sys_id = Cache::remember('sys_id_client_id'.request()['oauth_client_id'],
            60, function () {
                return Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id;
            });
        return $this->hasOne(CasinoGamesDescription::class, 'casino_game_id', 'id')->where([
            'active' => 1,
            'sys_id' => $sys_id,
            'lang' => 'en-us'
        ]);
    }

    public function transaction(){
        return $this->hasMany(CasinoGamesTransaction::class, 'gameId', request()->is_mobile?$this->code_mobile_column:$this->code_column)
            ->where('usr_id','=',Auth::user()->usr_id);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function casino_games_category() {
        $sys_id = Cache::remember('sys_id_client_id'.request()['oauth_client_id'],
            60, function () {
                return Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id;
            });

        return $this->hasOne(CasinoGamesCategory::class, 'casino_games_id', 'id')
            ->where('sys_id', '=', $sys_id);

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function casino_games_category_clients() {
        $sys_id = Cache::remember('sys_id_client_id'.request()['oauth_client_id'],
            60, function () {
                return Client::where('id', request()['oauth_client_id'])->first()->site->system->sys_id;
            });

        return $this->hasOne(CasinoGamesCategory::class, 'casino_games_id', 'id')
            ->where('sys_id', '=', $sys_id)
            ->whereIn(
                'casino_games_id', self::client_casino_games(1)
                ->pluck('product_id')
            )
            ->orderBy('popular_game', 'desc')
            ->orderBy('order');

    }

    public function user_active_game_bonus(){
        return Auth::user()->casino_bonus_user()
            ->whereHas('bonus_category.casino_category.games_category',function ($query) {
                $query->where('casino_games_id', '=', $this->id);
            })->first();
    }

    public function getUserGameBalanceAttribute(){
        $getBalanceUserService = new GetBalanceUserService();
        $balanceUser = $getBalanceUserService->execute($this);
        return $balanceUser['balance_user_bonus_game'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function casino_games_bet_config() {
        return $this->hasMany(CasinoGamesBetConfig::class, 'casino_games_id', 'id');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getDescriptionAttributesAttribute() {
        $descriptions = collect([]);
        $this->description->each(function ($item, $key) use ($descriptions) {

            $lines = (isset($this->lines) && $this->lines != 0) ? $this->lines : '';
            $multiplier = (isset($this->multiplier) && $this->multiplier != 0) ? $this->multiplier : '';
            $currency = Auth::user() ? Auth::user()->curr_code : request()['country_currency'];

            $bet_config = $this->casino_games_bet_config->where('curr_code','=',$currency)->first();

            $min_bet = is_null($bet_config) ? 0 : $bet_config->min_bet;
            $max_bet = is_null($bet_config) ? 0 : $bet_config->max_bet;
            $min_coin_value = is_null($bet_config) ? 0 : $bet_config->min_bet;
            $max_coin_value = is_null($bet_config) ? 0 : $bet_config->max_bet;

            if($lines !== ''){
                $max_bet = $max_bet * $lines;
            }
            if($multiplier !== ''){
                $max_bet = $max_bet * $multiplier;
            }
            $search = array('#MIN_BET#','#MAX_BET#', '#MIN_COIN_VALUE#','#MAX_COIN_VALUE#','#LINES#','#MULTIPLIER#','#CURRENCY#');
            $replace = array($min_bet, $max_bet, $min_coin_value, $max_coin_value, $lines, $multiplier, $currency);
            $item->how_to_win = str_replace($search, $replace, $item->how_to_win);

            $description = $item->transformer ? $item->transformer::transform($item) : $item;
            $descriptions->push($description);
        });
        return $descriptions;
    }

    /**
     * @return mixed
     */
    public function getGameCodeAttributesAttribute() {
        if ($this->casino_provider_id == CasinoProvider::MULTISLOT_CASINO_PROVIDER) {
            $game_code = $this->external_casino_id;
        } elseif ($this->casino_provider_id == CasinoProvider::ORYX_CASINO_PROVIDER) {
            $game_code = $this->game_code;
        }elseif ($this->casino_provider_id == CasinoProvider::REDTIGER_CASINO_PROVIDER) {
            $game_code = $this->game_code;
        }
        return $game_code;
    }

    public function getRealExternalCasinoProviderId() {
        return $this->attributes["external_casino_id"];
    }

    public function getRealCasinoProviderId() {
        return $this->attributes["casino_provider_id"];
    }

    public function getRealGameCode() {
        return $this->attributes["game_code"];
    }

    public function getRealGameCodeMobile() {
        return $this->attributes["game_code_mobile"];
    }

    public function getCasinoProviderAttributesAttribute() {
        return $this->provider? $this->provider->id : null;
    }

    public function getDemoUrlAttribute() {
        $generateDemoUrl = new GenerateUrlDemoCasinoGameService();
        return $generateDemoUrl->execute( $this->id, $this->live );
    }

    public function getRealPlayUrlAttribute() {
        $generateRealPlayUrl = new GenerateUrlRealPlayCasinoGameService();
        return $generateRealPlayUrl->execute($this->id);
    }

    public function getCodeAttribute(){
        return $this->casino_provider_id==CasinoProvider::MULTISLOT_CASINO_PROVIDER ? $this->external_casino_id : $this->game_code;
    }

    public function getCodeMobileAttribute(){
        if ($this->casino_provider_id==CasinoProvider::MULTISLOT_CASINO_PROVIDER){
            return $this->game_code_mobile?$this->game_code_mobile:$this->external_casino_id;
        }elseif ($this->casino_provider_id==CasinoProvider::ORYX_CASINO_PROVIDER) {
            return $this->game_code_mobile ? $this->game_code_mobile : null;
        }elseif ($this->casino_provider_id==CasinoProvider::REDTIGER_CASINO_PROVIDER) {
            return $this->game_code;
        }
    }
    public function getCodeColumnAttribute(){
        return $this->casino_provider_id==CasinoProvider::MULTISLOT_CASINO_PROVIDER ? 'external_casino_id' : 'game_code';
    }

    public function getCodeMobileColumnAttribute(){
        if ($this->casino_provider_id==CasinoProvider::MULTISLOT_CASINO_PROVIDER){
            return $this->game_code_mobile?'game_code_mobile':'external_casino_id';
        }elseif ($this->casino_provider_id==CasinoProvider::ORYX_CASINO_PROVIDER) {
            return 'game_code_mobile';
        }
    }

    public function getHasOpenTransactionAttribute(){
        return $transaction = $this->transaction->where('transactionStatus','=',CasinoGamesTransaction::OPEN_TRANSACTION_STATUS)
            ->where('transactionType','=',CasinoGamesTransaction::BET_TRANSACTION_TYPE)
            ->where('casino_provider_id','=',$this->casino_provider_id)
            ->first()?true:false;
    }


    public function getBetConfigUserCurrencyAttribute(){
        return $this->casino_games_bet_config->where('curr_code','=',Auth::user()->curr_code)->first();
    }

    public function canOpen(){
        // Black List by Country

        $origin = GetOriginRequestService::execute();
        $activeExceptionDomain = env('DOMAIN_STATIC_EXCEPTION',null) === $origin;
        if($activeExceptionDomain === true){
            return true;
        }

        if (! ApiResponser::client_casino_games(1)->pluck('product_id')->contains($this->id))
            return false;

        if (($this->is_flash && request()->is_mobile)||// No flash game in mobile
            !$this->game_enabled || // game disabled
            $this->game_blocked || // game blocked
            !$this->casino_provider_attributes) // provider disabled
            return false;

        // request desktop with no desktop code, request mobile with no mobile code
        if (!request()->is_mobile && !$this->code  || request()->is_mobile && !$this->code_mobile){
            return false;
        }
        return true;
    }

    public function saveToken($token){
        $casino_game_token = new CasinoGamesToken();
        $casino_game_token->token               = $token;
        $casino_game_token->usr_id              = Auth::user()->usr_id;
        $casino_game_token->session_id          = md5(Auth::user()->usr_id);
        $casino_game_token->site_id             = Client::where('id', request()['oauth_client_id'])->first()->site_id;
        $casino_game_token->casino_game_id      = $this->id;
        $casino_game_token->casino_category_id  = $this->casino_games_category()->first()->casino_category_id;
        $casino_game_token->game_id             = request()->is_mobile?$this->code_mobile:$this->code;
        $casino_game_token->save();
    }
}
