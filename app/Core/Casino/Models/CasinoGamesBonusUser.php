<?php

namespace App\Core\Casino\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CasinoGamesBonusUser extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'casino_bonus_user';
    public $timestamps = false;
    const CREATED_AT = 'reg_date';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'casino_bonus_id',
        'initial_amount',
        'initial_wr',
        'amount',
        'wr',
        'curr_code',
        'status',
        'reg_date'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'casino_bonus_id',
        'usr_id',
        'crt_id',
        'initial_amount',
        'initial_wr',
        'amount',
        'wr',
        'curr_code',
        'status',
        'reg_date'
    ];

}
