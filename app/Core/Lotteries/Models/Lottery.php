<?php

namespace App\Core\Lotteries\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Countries\Models\Region;
use App\Core\Rapi\Models\AlertMailsData;
use App\Core\Rapi\Models\Draw;
use App\Http\Controllers\CurrencyController;
use App\Core\Base\Services\ClientService;
use App\Core\Lotteries\Services\GetMaxLottoAttributesService;
use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Base\Traits\LogCache;
use App\Core\Base\Traits\Utils;
use App\Core\Lotteries\Models\LotteriesBoostedJackpot;
use App\Core\Lotteries\Models\LotteryExtraInfo;
use App\Core\Lotteries\Models\LotteryLevel;
use App\Core\Lotteries\Models\LotteryPrize;
use App\Core\Rapi\Models\Price;
use App\Core\Lotteries\Models\PriceModelCountry;
use App\Core\Rapi\Models\ProductType;
use App\Core\Rapi\Models\RoutingFriendly;
use App\Core\Lotteries\Transforms\LotteryTransformer;
use App\Core\Countries\Transforms\RegionTransformer;
use App\Core\Rapi\Models\Wheel;
use Illuminate\Support\Facades\Config;

class Lottery extends CoreModel
{
    use CartUtils;
    use Utils;
    use LogCache;

    public $timestamps = false;
    public $transformer = LotteryTransformer::class;
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'lot_id';

    public static $CASH4LIFE_ID = 48;
    public static $LA_PRIMITIVA = 19;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lot_name_en',
        'curr_code',
        'lot_sun',
        'lot_mon',
        'lot_tue',
        'lot_wed',
        'lot_thu',
        'lot_fri',
        'lot_sat',
        'lot_balls',
        'lot_extra',
        'lot_reintegro',
        'lot_pick_balls',
        'lot_pick_extra',
        'lot_pick_reintegro',
        'lot_maxNum',
        'lot_extra_maxNum',
        'lot_extra_name_en',
        'lot_extra_startNum',
        'slip_min_lines',
        'lot_raffle_number',
        'lot_region',
        'lot_logo',
        'lot_link',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'lot_id',
        'lot_name_en',
        'curr_code',
        'lot_sun',
        'lot_mon',
        'lot_tue',
        'lot_wed',
        'lot_thu',
        'lot_fri',
        'lot_sat',
        'lot_balls',
        'lot_extra',
        'lot_reintegro',
        'lot_pick_balls',
        'lot_pick_extra',
        'lot_pick_reintegro',
        'lot_maxNum',
        'lot_extra_maxNum',
        'lot_extra_name_en',
        'lot_extra_startNum',
        'slip_min_lines',
        'lot_raffle_number',
        'region_attributes',
        'product_type_id',
        'lot_logo',
        'lot_link',
        'draw_attributes',
        'prices_list',
        'insure_boosted_jackpot'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region() {
        return $this->belongsTo(Region::class, 'lot_region', 'reg_id');
    }

    public function lotteriesBoostedJackpot() {
        return $this->hasMany(LotteriesBoostedJackpot::class, 'lot_id', 'lot_id');
    }

    public function routingFriendly() {
        return $this->hasOne(RoutingFriendly::class, 'element_id', 'lot_id')
            ->where('element_type', RoutingFriendly::ELEMENT_LOTTERY)
            ->where('lang', app()->getLocale())
            ->where('sys_id', request()->client_sys_id);
    }

    public function getRoutingFriendlyAttributesAttribute() {
        return SetTransformToModelOrCollectionService::execute($this->routingFriendly);
    }

    public function getBoostedJackpotAttributesAttribute() {
        return SetTransformToModelOrCollectionService::execute($this->lotteriesBoostedJackpot);
    }

    /**
     * @return RegionTransformer
     */
    public function getRegionAttributesAttribute() {
        return $this->rememberCache('lottery_region_' . $this->getLanguage() . '_' . $this->lot_id, Config::get('constants.cache_daily'), function () {
            $region = $this->region;
            return $region ? $region->transformer::transform($region) : null;
        });
    }

    public function draw_active() {
        return $this->hasOne(Draw::class, 'lot_id', 'lot_id')
            ->where('draw_status', '=', 0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices() {
        if(ClientService::isOrca()){ // es orca
            return $this->hasMany(Price::class, 'lot_id', 'lot_id')
                ->with('price_lines');
                //->where('active', '=', 1)
                //->where('sys_id', '=', request('client_sys_id'));
        }
        $price_models = PriceModelCountry::query()->where('product_type', '=', 1)
            ->whereIn('country_id', [0, request('client_country_id')])
            ->where('site_id', '=', request('client_site_id'))->getFromCache();
        $relation = $this->hasMany(Price::class, 'lot_id', 'lot_id')
            ->with('price_lines')
            ->where('active', '=', 1)
            ->where('sys_id', '=', request('client_sys_id'));

        if($this->lot_id  == self::$CASH4LIFE_ID){ //Si es Cash4life price_model es solo el 1
            $relation = $relation->where('prc_model_type', '=', 1);
        }else{
            if ($price_models && $price_models->isNotEmpty()) {
                $relation = $relation->where('prc_model_type', '>', 0);
            } else {
                $relation = $relation->where('prc_model_type', '=', 0)
                    ->where('prc_time', '>', 0);
            }
        }

        return $relation->orderBy('prc_draws', 'asc')
            ->orderBy('prc_min_jackpot', 'desc');
    }

    /**
     * @return \App\Core\Rapi\Models\ProductType
     */
    public function getProductTypeIdAttribute() {
        return ProductType::where('type', 'Lottery')->first()->id;
    }

    public function getActiveDraw() {
        return $this->draws->where('draw_status', 0)->filter(function ($value, $key) {
            return $value->draw_date . ' ' . $value->draw_time > date('Y-m-d H:i:s');
        })->first();
    }

    /**
     * @return Draw
     */
    public function getActiveDrawAttributesAttribute() {
        $active_draw = $this->draw_active;
        return $active_draw ? $active_draw->transformer::transform($active_draw) : $active_draw;
    }

    public function oldDraws() {
        return $this->draws()->where('draw_status', '=', 1)->orderByDesc('draw_date');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function draws() {
        return $this->hasMany(Draw::class, 'lot_id', 'lot_id');
    }

    public function prev_draw() {
        return $this->hasOne(Draw::class, 'lot_id', 'lot_id')
            ->where('draw_status', '=', 1)
            ->latest("draw_id");
    }

    /**
     * @return mixed
     */
    public function getPricesListAttribute() {
        $active_draw = $this->draw_active;
        $jackpot = $active_draw ? $active_draw->draw_jackpot : null;
        $prices = collect([]);
        $this->prices->each(function ($item, $key) use ($prices, $jackpot) {
            if ($item->prc_min_jackpot > $jackpot || $item->prc_min_jackpot === 0) {
                $last_price = $prices->last();
                if ($last_price && $last_price->prc_draws === $item->prc_draws && $last_price->prc_time
                    === $item->prc_time) {
                    if ($item->prc_min_jackpot != 0) {
                        $prices->pop();
                        $prices->push($item);
                    }
                } else {
                    $prices->push($item);
                }
            }
        });
        return $prices;
    }

    public function getJackpotAttribute() {
        $active_draw = $this->draw_active;

        $resultMaxLotto = GetMaxLottoAttributesService::execute($this);
        if($resultMaxLotto !== null){
           return $resultMaxLotto->draw_jackpot;
        }

        if ($active_draw) {
            $date_actual = new \DateTime($active_draw->draw_date . ' ' . $active_draw->draw_time);
            $time_process = explode(':', $active_draw->draw_time_process);
            $existKeys = array_key_exists(0, $time_process) && array_key_exists(1, $time_process) &&
                array_key_exists(2, $time_process);
            if($existKeys === false){
                return -1;
            }
            $process_interval = new \DateInterval("PT{$time_process[0]}H{$time_process[1]}M{$time_process[2]}S");
            $date_actual->sub($process_interval);
            if ($date_actual->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')) {
                if (request("client_site_id") == 1015 && $this->curr_code != 'MXN') {
                    $factor = $this->convertCurrency($this->curr_code, 'MXN');
                    return (integer)($active_draw->draw_jackpot * $factor);
                }
                return $active_draw->draw_jackpot;
            }
        }
        return -1;
    }

    public function getJackpotUsdAttribute() {
        $active_draw = $this->draw_active;
        $resultMaxLotto = GetMaxLottoAttributesService::execute($this);
        if($resultMaxLotto !== null){
            return $resultMaxLotto->usd_jackpot;
        }
        if ($active_draw) {
            $date_actual = new \DateTime($active_draw->draw_date . ' ' . $active_draw->draw_time);
            $time_process = explode(':', $active_draw->draw_time_process);
            $existKeys = array_key_exists(0, $time_process) && array_key_exists(1, $time_process) &&
                array_key_exists(2, $time_process);
            if($existKeys === false){
                return -1;
            }
            $process_interval = new \DateInterval("PT{$time_process[0]}H{$time_process[1]}M{$time_process[2]}S");
            $date_actual->sub($process_interval);
            if ($date_actual->format('Y-m-d H:i:s') > date('Y-m-d H:i:s')) {
                if ($this->curr_code != 'USD') {
                    $factor = $this->convertCurrency($this->curr_code, 'USD');
                    return (integer)($active_draw->draw_jackpot * $factor);
                }
                return $active_draw->draw_jackpot;
            }
        }
        return -1;
    }

    public function days_to_play() {
        return $this->lot_sun + $this->lot_mon + $this->lot_tue + $this->lot_wed + $this->lot_thu + $this->lot_fri + $this->lot_sat;
    }

    public function levels() {
        return $this->hasMany(LotteryLevel::class, 'lot_id', 'lot_id')
            ->orderBy('lol_order');
    }

    public function getJackpotChangeAttribute() {
        $active_draw = $this->draw_active;
        $prev_draw = $this->prev_draw;
        $prev_draw_jackpot = $prev_draw ? $prev_draw->draw_jackpot : 0;
        return $active_draw ? $active_draw->draw_jackpot - $prev_draw_jackpot : null;
    }

    public function lottery_prizes($draw) {
        return $draw ? $this->prizes()->where('draw_id', '=', $draw->draw_id)->get() : collect([]);
    }

    public function prizes() {
        return $this->hasMany(LotteryPrize::class, 'lot_id', 'lot_id');
    }

    public function lottery_extra_info() {
        return $this->hasOne(LotteryExtraInfo::class, 'lot_id', 'lot_id');
    }

    public function wheel() {
        return $this->hasOne(Wheel::class, 'wheel_id', 'wheel_id');
    }

    public function getFancyNameAttribute() {
        $lottery_extra_info = $this->lottery_extra_info;
        $name = 'name_fancy_' . $this->getLanguage();
        return $lottery_extra_info ? $lottery_extra_info->$name : null;
    }

    public function getDrawDateAttribute() {
        $active_draw = $this->draw_active;
        $resultMaxLotto = GetMaxLottoAttributesService::execute($this);
        if($resultMaxLotto !== null){
            $idLottery = $resultMaxLotto->lot_id;
            $lottery = Lottery::query()
                ->where('lot_id', $idLottery)
                ->with('draw_active.lottery')
                ->firstFromCache();
            $active_draw = $lottery->draw_active;
        }
        if ($active_draw) {
            $date_actual = new \DateTime($active_draw->draw_date . ' ' . $active_draw->draw_time);
            $time_process = explode(':', $active_draw->draw_time_process);
            $existKeys = array_key_exists(0, $time_process) && array_key_exists(1, $time_process) &&
                array_key_exists(2, $time_process);
            if($existKeys === false){
                return null;
            }
                $process_interval = new \DateInterval("PT{$time_process[0]}H{$time_process[1]}M{$time_process[2]}S");
                $date_actual->sub($process_interval);
                return $date_actual->format('Y-m-d H:i:s') > date('Y-m-d H:i:s') ? $date_actual->format('Y-m-d H:i:s') : $active_draw->draw_dates;
        }

        return null;
    }
    public function getBigLottoAttribute() {
        $resultMaxLotto = GetMaxLottoAttributesService::execute($this);
        if($resultMaxLotto !== null){
            return $resultMaxLotto->lot_id;
        }
        return null;
    }

    public function getCurrencyAttribute() {
        $resultMaxLotto = GetMaxLottoAttributesService::execute($this);
        if($resultMaxLotto !== null){
            return $resultMaxLotto->curr_code;
        }
        if (request("client_site_id") == 1015) {
            return 'MXN';
        }
        return $this->curr_code;
    }

    public function getNameAttribute() {
        $name = 'lot_name_' . $this->getLanguage();
        return $this->$name ? $this->$name : $this->lot_name_en;
    }

    public function getExtraNameAttribute() {
        $extra_name = 'lot_extra_name_' . $this->getLanguage();
        return $this->$extra_name ? $this->$extra_name : $this->lot_extra_name_en;
    }

    /**
     * @return \Illuminate\Support\Collection|int
     */
    public function getAlertMailsAttribute() {
        $alerts_mails_data = $this->alerts_mails_data()->whereHas('alert_mails_user');
        $alerts = collect([]);
        $alerts_mails_data->each(function ($item, $key) use ($alerts) {
            if ($item->send_results == 1 || $item->send_jackpot == 1) {
                $alerts_mails = $item->transformer ? $item->transformer::transform($item) : $item;
                $alerts->push($alerts_mails);
            }
        });
        return $alerts->isNotEmpty() ? $alerts : $alerts->push(['result' => 0, 'jackpot' => 0]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alerts_mails_data() {
        return $this->hasMany(AlertMailsData::class, 'lot_id', 'lot_id');
    }

    public function isCash4Life(){
        if($this->lot_id == self::$CASH4LIFE_ID){
            return true;
        }
        return false;
    }

    public function getMaxIndividualDrawAttribute() {

        $sum = $this->lot_sun + $this->lot_mon + $this->lot_tue + $this->lot_wed + $this->lot_thu + $this->lot_fri + $this->lot_sat;

        $maxIndividualDraw = $sum * 4;
        if($this->lot_id == 19){
            $maxIndividualDraw /= 2;
        }
        return $maxIndividualDraw;
    }
}
