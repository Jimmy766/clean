<?php

namespace App\Core\Users\Models;

use App\Core\Base\Models\CoreModel;

/**
 * Class UserOneTimeRejected
 * @package App
 */
class UserOneTimeRejected extends CoreModel
{

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public    $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded    = [];

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    public $connection = 'mysql_external';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'usr_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_onetime_rejected';
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
