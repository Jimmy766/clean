<?php

namespace App\Core\Lotteries\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Countries\Models\Country;
use App\Core\Rapi\Models\Site;

class PriceModelCountry extends CoreModel
{
    protected $guarded=[];
    //public $transformer = ContinentTransformer::class;
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
    protected $hidden= [

    ];

    protected $table = 'price_model_country';

    public function site() {
        return $this->belongsTo(Site::class, 'site_id', 'site_id');
    }

    public function country() {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }
}
