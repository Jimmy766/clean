<?php

namespace App\Core\Clients\Models;

use App\Core\Base\Models\CoreModel;

class Ip2Location extends CoreModel
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'ip2location';
    const CREATED_AT = 'log_date';
    const UPDATED_AT = null;


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
