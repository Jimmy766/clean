<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Base\Traits\LogCache;
use App\Core\Countries\Models\Country;
use App\Core\Rapi\Transforms\StateTransformer;
use Illuminate\Support\Facades\Config;

class State extends CoreModel
{
    use LogCache;
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'state_id';
    public $timestamps = false;
    public $transformer = StateTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state_name', 'state_iso'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'state_id', 'state_name', 'state_iso', 'country_attributes',
    ];



    public function country() {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    function getCountryAttributesAttribute() {
        $country = $this->rememberCache('state_country_'.$this->state_id, Config::get('constants.cache_daily'), function() {
            $country = $this->country;
            return $country->transformer::transform($country);
        });
        return $country;
    }
}
