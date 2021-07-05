<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\PayoutMethodTransformer;
use Illuminate\Database\Eloquent\Model;

class PayoutMethod extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'payout_id';
    public $timestamps = false;
    public $transformer = PayoutMethodTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'tag_name',
        'pay_id',
        'country_id',
        'black_country_list',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'payout_id',
        'name',
        'tag_name',
        'pay_id',
        'country_id',
        'black_country_list',
    ];

}
