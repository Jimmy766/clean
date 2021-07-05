<?php

namespace App\Core\Lotteries\Models;

use Illuminate\Database\Eloquent\Model;

class LotteryExtraInfo extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'id';
    protected $table = 'lotteries_extra_info';
    public $timestamps = false;
    public $transformer = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lot_id',
        'name_fancy_en',
        'max_game_exposure',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'lot_id',
        'name_fancy_en',
        'max_game_exposure',
    ];
}
