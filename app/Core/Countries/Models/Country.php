<?php

namespace App\Core\Countries\Models;

use App\Core\Base\Models\CoreModel;
use App\Core\Base\Traits\Utils;
use App\Core\Countries\Models\Region;
use App\Core\Rapi\Models\State;
use App\Core\Countries\Models\CountryInfo;
use App\Core\Countries\Transforms\CountryTransformer;
use Illuminate\Database\Eloquent\Model;


class Country extends CoreModel
{
    use Utils;


    protected $guarded=[];
    protected $table      = 'countries';
    public $connection = 'mysql_external';
    protected $primaryKey = 'country_id';
    public $timestamps = false;
    public $transformer = CountryTransformer::class;
    protected $fillable    = [
        'country_id',
        'country_Iso',
        'country_name_en',
        'country_name_es',
        'country_name_pt',
    ];


    public function states() {
        return $this->hasMany(State::class, 'country_id', 'country_id');
    }

    public function regions() {
        return $this->hasMany(Region::class, 'country_id', 'country_id');
    }

    public function country_info() {
        return $this->hasOne(CountryInfo::class, 'country_id', 'country_id');
    }

    public function getNameAttribute() {
        $name = 'country_name_'.app()->getLocale();
        return $this->$name ? $this->$name : $this->country_name_en;
    }

}
