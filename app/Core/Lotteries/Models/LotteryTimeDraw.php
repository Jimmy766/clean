<?php

namespace App\Core\Lotteries\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryTimeDraw extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lot_id',
        'prc_time',
        'prc_time_type',
        'prc_draws',
        'price_id',
        'prc_days'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'lot_id',
        'prc_time',
        'prc_time_type',
        'prc_draws',
        'price_id',
        'prc_days'
    ];
}
