<?php

namespace App\Core\Casino\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *   @SWG\Definition(
 *     definition="CasinoGamesBetConfig",
 *     required={"curr_code","min_bet","max_bet"},
 *     @SWG\Property(
 *       property="curr_code",
 *       type="string",
 *       description="Currency of the bet confing",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="min_bet",
 *       type="number",
 *       format="float",
 *       description="Minimum bet",
 *       example="1.2"
 *     ),
 *     @SWG\Property(
 *       property="max_bet",
 *       type="number",
 *       format="float",
 *       description="Maximum bet",
 *       example="50.0"
 *     ),
 *   )
 */
class CasinoGamesBetConfig extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'casino_games_bet_config';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'curr_code',
        'min_bet',
        'max_bet'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'curr_code',
        'min_bet',
        'max_bet',
        'casino_games_id'
    ];

}
