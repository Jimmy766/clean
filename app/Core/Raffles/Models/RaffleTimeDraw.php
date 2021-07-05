<?php

namespace App\Core\Raffles\Models;

use Illuminate\Database\Eloquent\Model;

class RaffleTimeDraw extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    protected $table = 'raffles_time_draws';
    //public $transformer = RaffleTicketTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];
}
