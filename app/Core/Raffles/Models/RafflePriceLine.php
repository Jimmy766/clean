<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Models\RafflePrice;
use App\Core\Raffles\Transforms\RafflePriceLineTransformer;
use Illuminate\Database\Eloquent\Model;

class RafflePriceLine extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'prcln_rff_id';
    public $timestamps = false;
    protected $table = 'prices_line_raffles';
    public $transformer = RafflePriceLineTransformer::class;

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

    public function price() {
        return $this->belongsTo(RafflePrice::class, 'prc_rff_id', 'prc_rff_id');
    }

    public function getCountryListEnabledAttribute() {
        return $this->prcln_rff_country_list_enabled === '0' ? [] : explode(',', $this->prcln_rff_country_list_enabled);
    }

    public function getCountryListDisabledAttribute() {
        return $this->prcln_rff_country_list_disabled === '0' ? [] : explode(',', $this->prcln_rff_country_list_disabled);
    }
}
