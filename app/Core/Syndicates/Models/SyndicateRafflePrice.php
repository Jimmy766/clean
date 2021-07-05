<?php

namespace App\Core\Syndicates\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Syndicates\Models\SyndicateRaffle;
use App\Core\Syndicates\Models\SyndicateRafflePriceLine;
use App\Core\Syndicates\Transforms\SyndicateRafflePriceTransformer;
use Illuminate\Database\Eloquent\Model;

class SyndicateRafflePrice extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'prc_id';
    public $timestamps = false;
    protected $table = 'syndicate_raffle_prices';
    public $transformer = SyndicateRafflePriceTransformer::class;
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

    public function price_lines() {
        $currency = request('country_currency');
        return $this->hasMany(SyndicateRafflePriceLine::class, 'prc_id', 'prc_id')
            ->where('curr_code','=', $currency);
    }

    public function syndicate_raffle() {
        return $this->belongsTo(SyndicateRaffle::class, 'rsyndicate_id','id');
    }

    public function getPriceLineAttribute() {
        $country_id = request('client_country_id');
        $currency = request('country_currency');
        $priceLine = SyndicateRafflePriceLine::query()
            ->where('prc_id', $this->prc_id)
            ->where('curr_code', $currency)
            ->getFromCache();
        $price_line = $priceLine->filter(function ($item) use ($country_id) {
            return (!empty($item->country_list_disabled) && !in_array($country_id, $item->country_list_disabled)) //no esta en los disabled
                || (!empty($item->country_list_enabled) && in_array($country_id, $item->country_list_enabled)) // esta en los enabled
                || (empty($item->country_list_enabled) && empty($item->country_list_disabled)); //esta para todos
        })->first();
        return $price_line;
    }

    public function getPriceAttribute() {
        return $this->price_line ? $this->price_line->prcln_price : null;
    }

    public function getCurrencyAttribute() {
        return $this->price_line ? $this->price_line->curr_code : null;
    }
}
