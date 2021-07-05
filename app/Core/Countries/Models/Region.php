<?php

namespace App\Core\Countries\Models;

use App\Core\Countries\Models\Continent;
use App\Core\Base\Traits\LogCache;
use App\Core\Base\Traits\Utils;
use App\Core\Countries\Models\Country;
use App\Core\Countries\Transforms\RegionTransformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;


class Region extends Model
{
    use Utils;
    use LogCache;

    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'reg_id';
    public $timestamps = false;
    public $transformer = RegionTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reg_name_en', 'country_id', 'cont_id', 'reg_flag',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'reg_id', 'reg_name_en', 'country_id', 'cont_id', 'country_attributes', 'continent_attributes', 'reg_flag'
    ];

    public function country() {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }

    public function continent() {
        return $this->belongsTo(Continent::class, 'cont_id', 'cont_id');
    }

    public function getCountryAttributesAttribute() {
        return $this->rememberCache('region_country_'.$this->getLanguage().'_'.$this->reg_id, Config::get('constants.cache_daily'), function() {
            $country = $this->country;
            return $country ? ($country->transformer ? $country->transformer::transform($country) : $country) : null;
        });
    }

    public function getContinentAttributesAttribute() {
        return $this->rememberCache('region_continent_'.$this->getLanguage().'_'.$this->reg_id, Config::get('constants.cache_daily'), function() {
            $continent = $this->continent;
            return $continent ? ($continent->transformer ? $continent->transformer::transform($continent) : $continent) : null;
        });
    }

    public function getNameAttribute() {
        $name = 'reg_name_'.$this->getLanguage();
        return $this->$name ? $this->$name : $this->reg_name_en;
    }

}
