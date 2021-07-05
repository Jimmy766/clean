<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\PriceLineTransformer;
use Illuminate\Database\Eloquent\Model;

class PriceLine extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'prices_line';
    protected $primaryKey = 'prcln_id';
    const CREATED_AT = 'prcln_date';
    const UPDATED_AT = 'prcln_lastupdate';
    public $transformer = PriceLineTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'prc_id', 'prcln_price', 'curr_code', 'prcln_discount', 'price_modifier_1', 'price_modifier_2', 'price_modifier_3',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'prcln_id', 'prcln_price', 'curr_code', 'prcln_discount', 'price_modifier_1', 'price_modifier_2', 'price_modifier_3', 'country_list_enabled', 'country_list_disabled',
    ];

    public function price() {
        return $this->belongsTo(Price::class, 'prc_id', 'prc_id');
    }

    public function getCountryListEnabledAttribute() {
        return $this->prcln_country_list_enabled === '0' ? [] : explode(',', $this->prcln_country_list_enabled);
    }

    public function getCountryListDisabledAttribute() {
        return $this->prcln_country_list_disabled === '0' ? [] : explode(',', $this->prcln_country_list_disabled);
    }
}
