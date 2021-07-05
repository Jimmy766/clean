<?php

namespace App\Core\Casino\Models;

use Illuminate\Database\Eloquent\Model;

class CasinoGamesBonusMovement extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'casino_bonus_movement';
    public $timestamps = false;
    const CREATED_AT = 'reg_date';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'expiration_date',
        'reg_date'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'promotion_id',
        'expiration_date',
        'reg_date'
    ];

}
