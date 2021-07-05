<?php

namespace App\Core\Lotteries\Models;

use App\Core\Lotteries\Transforms\BetTransformer;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'lotteries_bet_configuration';
    const CREATED_AT = 'reg_date';
    public $transformer = BetTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lot_id',
        'min_bet',
        'max_bet',
        'curr_code',
        'active',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'lot_id',
        'min_bet',
        'max_bet',
        'curr_code',
    ];
}
