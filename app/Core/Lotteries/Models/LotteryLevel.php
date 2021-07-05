<?php

namespace App\Core\Lotteries\Models;

use App\Core\Lotteries\Transforms\LotteryLevelTransformer;
use Illuminate\Database\Eloquent\Model;


class LotteryLevel extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'lol_id';
    protected $table = 'lotteries_levels';
    public $timestamps = false;
    public $transformer = LotteryLevelTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lot_id',
        'modifier_id',
        'lol_balls',
        'lot_extras',
        'lol_reintegro',
        'lol_prize',
        'lol_odds',
        'lol_order',
        'lol_prize_type',
        'lol_accepts_multiplier',
        'lol_amount_minimum',
        'lol_amount_maximum',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'lol_id',
        'lot_id',
        'modifier_id',
        'lol_balls',
        'lot_extras',
        'lol_reintegro',
        'lol_prize',
        'lol_odds',
        'lol_order',
        'lol_prize_type',
        'lol_accepts_multiplier',
        'lol_amount_minimum',
        'lol_amount_maximum',
    ];
}
