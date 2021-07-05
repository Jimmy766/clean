<?php

namespace App\Core\Lotteries\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryFirstDayToPlay extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    public $timestamps = false;
    protected $table = 'lottery_first_datetoplay';
    protected $primaryKey = "cts_id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cts_id',
        'first_datetoplay',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'cts_id',
        'first_datetoplay',
    ];

}
