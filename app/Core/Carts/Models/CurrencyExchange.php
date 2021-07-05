<?php

namespace App\Core\Carts\Models;

use App\Core\Base\Models\CoreModel;
use Illuminate\Database\Eloquent\Model;

class CurrencyExchange extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'exch_id';
    public $timestamps = false;
    protected $table = 'currency_exchange';
    //public $transformer = CountryTransformer::class;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'curr_code_from', 'curr_code_to', 'exch_factor',
    ];



}
