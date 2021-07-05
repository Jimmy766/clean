<?php

namespace App\Core\Raffles\Models;

use App\Core\Raffles\Transforms\RaffleResultTransformer;
use Illuminate\Database\Eloquent\Model;

class RaffleResult extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'prc_rff_id';
    public $timestamps = false;
    protected $table = 'raffles_results';
    public $transformer = RaffleResultTransformer::class;

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
