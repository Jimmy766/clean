<?php

namespace App\Core\Users\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogin extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'users_login';
    const CREATED_AT = 'log_date';
    const UPDATED_AT = null;


    protected $fillable = [
        'usr_id', 'log_ip',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'log_id', 'usr_id', 'log_ip', 'log_date'
    ];
}
