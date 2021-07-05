<?php

namespace App\Core\Rapi\Models;

use App\Core\Base\Models\CoreModel;

/**
 * Class CasinoFreeSpins
 * @package App
 */
class Proxy extends CoreModel
{
    public    $timestamps = false;
    protected $guarded    = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'ip';
    protected $table      = 'proxies';
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
