<?php

namespace App\Core\Lotteries\Models;

use App\Core\Lotteries\Transforms\LotteryPrizeTransformer;
use Illuminate\Database\Eloquent\Model;


class LotteryPrize extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'lop_id';
    protected $table = 'lotteries_prizes';
    public $timestamps = false;
    public $transformer = LotteryPrizeTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lot_id',
        'draw_id',
        'lop_balls',
        'lop_extras',
        'lop_reintegro',
        'lop_prize',
        'lop_prize_california',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'lop_id',
        'lot_id',
        'draw_id',
        'lop_balls',
        'lop_extras',
        'lop_reintegro',
        'lop_prize',
        'lop_prize_california',
    ];
}
