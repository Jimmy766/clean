<?php

namespace App\Core\Syndicates\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Base\Services\SetTransformToModelOrCollectionService;
use App\Core\Base\Traits\CartUtils;
use App\Core\Rapi\Models\RoutingFriendly;
use App\Core\Syndicates\Models\SyndicateRafflePrice;
use App\Core\Syndicates\Models\SyndicateRaffleRaffle;
use App\Core\Syndicates\Transforms\SyndicateRaffleTransformer;
use Illuminate\Database\Eloquent\Model;

class SyndicateRaffle extends CoreModel
{
    use CartUtils;
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'id';
    protected $table = 'syndicate_raffle';
    public $timestamps = false;
    public $transformer = SyndicateRaffleTransformer::class;

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

    public function routingFriendly() {
        return $this->hasOne(RoutingFriendly::class, 'element_id', 'id')
            ->where('element_type', RoutingFriendly::ELEMENT_SYNDICATE_RAFFLE)
            ->where('lang', app()->getLocale())
            ->where('sys_id', request()->client_sys_id);
    }

    public function getRoutingFriendlyAttributesAttribute() {
        return SetTransformToModelOrCollectionService::execute($this->routingFriendly);
    }

    public function syndicate_raffle_raffles() {
        return $this->hasMany(SyndicateRaffleRaffle::class, 'rsyndicate_id', 'id');
    }

    public function getJackpotAttribute() {
        $jackpot = 0;
        $this->syndicate_raffle_raffles->each(function (SyndicateRaffleRaffle $item) use (&$jackpot) {
            $raffle = $item->raffle ? $item->raffle : null;
            $draw = $raffle ? $raffle->draw_active : null;
            if($draw && $raffle->curr_code != request('country_currency')) {
                $factor = $this->convertCurrency($raffle->curr_code, request('country_currency'));
                $jackpot += $draw ? round($draw->rff_jackpot * $factor): 0;
            } else {
                $jackpot += $draw ? $draw->rff_jackpot : 0;
            }
        });
        return $jackpot;
    }

    public function getDateAttribute() {
        $date = null;
        $this->syndicate_raffle_raffles->each(function ($item) use (&$date) {
            $raffle = $item->raffle ? $item->raffle : null;
            $draw = $raffle ? $raffle->draw_active : null;
            $date = ($draw && ((!$date) || ($draw->rff_playdate < $date))) ? $draw->rff_playdate : $date;
        });
        return $date;
    }

    public function isActive() {
        return $this->syndicate_raffle_raffles->filter(function (SyndicateRaffleRaffle $item) {
            $raffle = $item->raffle ? $item->raffle : null;
            $draw = $raffle ? $raffle->draw_active : null;
            return $draw ? true : false;
        })->isEmpty() ? false : true;
    }

    public function getCurrencyAttribute() {
        return request('country_currency');
    }

    public function syndicate_raffle_prices() {
        return $this->hasMany(SyndicateRafflePrice::class, 'rsyndicate_id', 'id')
            ->where('active', '=', 1);
    }

    public function getPricesAttribute() {
        $syndicateRafflePricesCollection = collect([]);

        $syndicateRafflePrices = SyndicateRafflePrice::query()
            ->where('rsyndicate_id', $this->id)
            ->where('active', 1)
            ->getFromCache();
        $syndicateRafflePrices->each(function ($item) use ($syndicateRafflePricesCollection) {
            $price = $item->transformer::transform($item);
            $syndicateRafflePricesCollection->push($price);
        });
        return $syndicateRafflePricesCollection;
    }

    public function getSyndicateRaffleNameAttribute() {
        return '#SYNDICATE_RAFFLE_NAME_'.$this->name.'#';
    }
}
