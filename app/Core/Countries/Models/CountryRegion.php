<?php

namespace App\Core\Countries\Models;

use Illuminate\Database\Eloquent\Model;

class CountryRegion extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'country_region_id';
    public $timestamps = false;
    protected $table = 'countries_regions';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'country_region_id', 'country_region_code',
    ];


}
