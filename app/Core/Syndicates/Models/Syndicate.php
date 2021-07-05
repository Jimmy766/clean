<?php

namespace App\Core\Syndicates\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Syndicates\Services\GetJackpotToSyndicateService;
use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Rapi\Models\RoutingFriendly;
use App\Core\Syndicates\Models\SyndicateLotto;
use App\Core\Syndicates\Models\SyndicatePrice;
use App\Core\Syndicates\Transforms\SyndicateTransformer;
use Illuminate\Database\Eloquent\Model;


/**
 * @SWG\Definition(
 *     definition="LotterySyndicate",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Lottery identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of lottery",
 *       example="Powerball"
 *     ),
 *     @SWG\Property(
 *       property="sunday",
 *       type="integer",
 *       description="Play sunday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="monday",
 *       type="integer",
 *       description="Play monday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="tuesday",
 *       type="integer",
 *       description="Play tuesday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="wednesday",
 *       type="integer",
 *       description="Play wednesday",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="friday",
 *       type="integer",
 *       description="Play friday",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="saturday",
 *       type="integer",
 *       description="Play saturday",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="tickets",
 *       type="integer",
 *       description="Tickets",
 *       example="50"
 *     ),
 *     @SWG\Property(
 *       property="region",
 *       description="Region of lottery",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/Region"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="draw_date",
 *       description="Next draw date",
 *       type="string",
 *       format="date-time",
 *       example="2018-01-01 12:00:00",
 *     ),
 *  ),
 */


class Syndicate extends CoreModel
{
    use CartUtils;
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'id';
    protected $table = 'syndicate';
    public $timestamps = false;
    public $transformer = SyndicateTransformer::class;
    protected $curr_code = '';
    protected $jackpot = 0;
    protected $date = '';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'participations',
        'tickets_to_show',
        'name',
        'multi_lotto',
        'no_renew',
        'syndicate_pck_type'
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'participations',
        'tickets_to_show',
        'name',
        'multi_lotto',
        'no_renew',
        'syndicate_pck_type'
    ];

    public function routingFriendly() {
        return $this->hasOne(RoutingFriendly::class, 'element_id', 'id')
            ->where('element_type', RoutingFriendly::ELEMENT_SYNDICATE)
            ->where('lang', app()->getLocale())
            ->where('sys_id', request()->client_sys_id);
    }

    public function getRoutingFriendlyAttributesAttribute() {
        return SetTransformToModelOrCollectionService::execute($this->routingFriendly);
    }

    public function syndicate_lotteries() {
        return $this->hasMany(SyndicateLotto::class, 'syndicate_id', 'id');
    }

    public function syndicate_lottery() {
        return $this->hasOne(SyndicateLotto::class, 'syndicate_id', 'id');
    }

    public function getLotteriesAttribute() {
        $syndicate_lotteries = collect([]);
        $this->syndicate_lotteries->each(function ($item, $key) use ($syndicate_lotteries) {
            $lottery = [
                'identifier' => $item->lottery ? $item->lottery->lot_id : null,
                'name' => $item->lottery ? $item->lottery->name : null,
                'sunday' => $item->lottery ? $item->lottery->lot_sun : null,
                'monday' => $item->lottery ? $item->lottery->lot_mon : null,
                'tuesday' => $item->lottery ? $item->lottery->lot_tue : null,
                'wednesday' => $item->lottery ? $item->lottery->lot_wed : null,
                'thursday' => $item->lottery ? $item->lottery->lot_thu : null,
                'friday' => $item->lottery ? $item->lottery->lot_fri : null,
                'saturday' => $item->lottery ? $item->lottery->lot_sat : null,
                'tickets' => $item->tickets,
                'region' => $item->lottery ? $item->lottery->region ?
                    $item->lottery->region->transformer ? $item->lottery->region->transformer::transform($item->lottery->region) :
                        $item->lottery->region : null : null,
                'draw_date' => $item->active_draw->draw_dates,
            ];
            $syndicate_lotteries->push($lottery);
        });
        return $syndicate_lotteries->sortBy('draw_date')->values();
    }

    public function syndicate_prices() {
        return $this->hasMany(SyndicatePrice::class, 'syndicate_id', 'id')->where('active', '=', 1);
    }

    public function getChancesToWinAttribute() {
        $lottery_days = $this->syndicate_lotteries->first() ? $this->syndicate_lotteries->first()->lottery->days_to_play() : 0;
        return $this->multi_lotto == 0 ? $this->tickets_to_show * $lottery_days : $this->tickets_to_show;
    }

    public function getOriginalJackpotAttribute() {
        $getJackpotToSyndicateService = new GetJackpotToSyndicateService();
        [$jackpot, $date, $currCode] = $getJackpotToSyndicateService->execute( $this);
        return $jackpot;
    }

    public function getJackpotAttribute() {
        $getJackpotToSyndicateService = new GetJackpotToSyndicateService();
        [$jackpot, $date, $currCode] = $getJackpotToSyndicateService->execute( $this, 'USD');
        return $jackpot;
    }

    public function getCurrCodeAttribute() {
        $getJackpotToSyndicateService = new GetJackpotToSyndicateService();
        [$jackpot, $date, $currCode] = $getJackpotToSyndicateService->execute( $this, 'USD');
        return $currCode;
    }

    public function getDrawDateAttribute() {
        $getJackpotToSyndicateService = new GetJackpotToSyndicateService();
        [$jackpot, $date, $currCode] = $getJackpotToSyndicateService->execute( $this, 'USD');
        return $date;
    }

    public function getPricesListAttribute() {
        $prices = collect([]);
        $this->syndicate_prices->each(function ($item, $key) use ($prices) {
            $price = $item->transformer ? $item->transformer::transform($item) : $item;
            $prices->push($price);
        });
        return $prices->sortBy('draws')->values();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    public function getExclusiveProductPriceDetailAttribute() {
        $syndicate_price = $this->syndicate_prices()->where('prc_id','=',$this->exclusive_product_prc_id)->first();
        return $syndicate_price->transformer ? $syndicate_price->transformer::transform($syndicate_price) : $syndicate_price;
    }


    public function getWheelInfoAttribute(){
        return $this->syndicate_lotteries->first()->wheels->first();
    }
}
