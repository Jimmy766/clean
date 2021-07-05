<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class AlertMails extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $table = 'alerts_mails';
    protected $primaryKey = 'alertMail_id';
    const CREATED_AT = 'reg_date';
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alertMail_id',
        'mail',
        'usr_language',
        'reg_date',
        'sys_id',
        'confirmed',
        'token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'alertMail_id',
        'mail',
        'usr_language',
        'reg_date',
        'sys_id',
        'confirmed',
        'token',
    ];
}
