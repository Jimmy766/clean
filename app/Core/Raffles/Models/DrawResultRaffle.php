<?php

namespace App\Core\Raffles\Models;

use App\Core\Rapi\Transforms\DrawResultRaffleTransformer;
use Illuminate\Database\Eloquent\Model;


class DrawResultRaffle extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $transformer = DrawResultRaffleTransformer::class;
    public $timestamps = false;
    protected $table = 'draws_results_raffles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'draw_id',
        'result',
        'modifier_id',
        'lol_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'draw_id',
        'result',
        'modifier_id',
        'lol_id',
    ];

}
