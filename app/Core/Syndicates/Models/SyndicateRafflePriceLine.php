<?php

namespace App\Core\Syndicates\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Syndicates\Models\SyndicateRafflePrice;
use Illuminate\Database\Eloquent\Model;

class SyndicateRafflePriceLine extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'prcln_id';
    public $timestamps = false;
    protected $table = 'syndicate_raffle_prices_line';
    //public $transformer = SyndicatePriceTransformer::class;
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

    public function syndicate_raffle_price() {
        return $this->belongsTo(SyndicateRafflePrice::class, 'prc_id', 'prc_id');
    }

    public function getCountryListEnabledAttribute() {
        return $this->prcln_country_list_enabled === '0' ? [] : explode(',', $this->prcln_country_list_enabled);
    }

    public function getCountryListDisabledAttribute() {
        return $this->prcln_country_list_disabled === '0' ? [] : explode(',', $this->prcln_country_list_disabled);
    }
}
