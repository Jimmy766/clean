<?php

namespace App\Core\FreeSpin\Models;

use App\Core\Base\Models\CoreModel;

/**
 * Class CasinoFreeSpinsPromotions
 * @package App
 */
class CasinoFreeSpinsPromotions extends CoreModel
{
    public    $timestamps = false;
    protected $guarded    = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'id';
    protected $table      = 'casino_freespins_promotions';
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
