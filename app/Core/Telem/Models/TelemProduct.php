<?php

namespace App\Core\Telem\Models;

use Illuminate\Database\Eloquent\Model;

class TelemProduct extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'telem_products';
    public $timestamps = false;
    protected $primaryKey = "tp_id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tp_id',
        'group_id',
        'tp_lotteries',
        'tp_syndicates',
        'tp_syndicate_raffles',
        'tp_raffles',
        'tp_lotteries_wheels_full',
        'tp_lotteries_wheels',
        'tp_syndicates_wheels',
        'tp_lotteries_presale',
        'tp_syndicate_raffles_presale',
        'tp_raffles_presale',
        'tp_lotteries_wheels_full_presale',
        'tp_lotteries_wheels_presale',
        'tp_syndicates_wheels_presale',

    ];
}
