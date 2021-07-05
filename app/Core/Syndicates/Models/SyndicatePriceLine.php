<?php

namespace App\Core\Syndicates\Models;

use App\Core\Syndicates\Models\SyndicatePrice;
use App\Core\Syndicates\Transforms\SyndicatePriceLineTransformer;
use Illuminate\Database\Eloquent\Model;



class SyndicatePriceLine extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'syndicate_prices_line';
    protected $primaryKey = 'prcln_id';
    const CREATED_AT = 'prcln_date';
    const UPDATED_AT = 'prcln_lastupdate';
    public $transformer = SyndicatePriceLineTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prc_id',
        'prcln_price',
        'curr_code',
        'prcln_country_list_disabled',
        'prcln_country_list_enabled',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'prcln_id',
        'prc_id',
        'prcln_price',
        'curr_code',
        'country_list_disabled',
        'country_list_enabled',
    ];

    public function price() {
        return $this->belongsTo(SyndicatePrice::class, 'prc_id', 'prc_id');
    }

    public function getCountryListEnabledAttribute() {
        return $this->prcln_country_list_enabled === '0' ? [] : explode(',', $this->prcln_country_list_enabled);
    }

    public function getCountryListDisabledAttribute() {
        return $this->prcln_country_list_disabled === '0' ? [] : explode(',', $this->prcln_country_list_disabled);
    }
}
