<?php

namespace App\Core\FreeSpin\Models;

use App\Core\Base\Models\CoreModel;

class OneTimeAllowedEmails extends CoreModel
{
    public    $timestamps = false;
    protected $guarded    = [];
    public $connection = 'mysql_external';
    protected $primaryKey = 'email';
    protected $table      = 'onetime_allowed_emails';
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
