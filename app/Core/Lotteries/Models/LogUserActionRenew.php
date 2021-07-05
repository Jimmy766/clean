<?php

namespace App\Core\Lotteries\Models;

use Illuminate\Database\Eloquent\Model;

class LogUserActionRenew extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $table = 'log_users_action_renew';
    const CREATED_AT = 'action_date';
    const UPDATED_AT = null;

    protected $fillable = [
        'usr_id',
        'product_type',
        'sub_id',
        'action_date',
        'sub_renew_before',
        'sub_renew_after',
        'ip'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [

    ];
}
