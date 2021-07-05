<?php

namespace App\Core\Countries\Models;

use Illuminate\Database\Eloquent\Model;

class CountryInfo extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'country_id';
    public $timestamps = false;
    protected $table = 'countries_info';
    //public $transformer = CountryTransformer::class;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'country_id', 'country_currency',
    ];


}
