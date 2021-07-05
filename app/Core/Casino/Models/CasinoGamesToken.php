<?php

namespace App\Core\Casino\Models;

use Illuminate\Database\Eloquent\Model;

class CasinoGamesToken extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'multislot_token';
    public $timestamps = false;
    const CREATED_AT = 'reg_date';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'usr_id',
        'session_id',
        'game_id',
        'casino_game_id',
        'casino_category_id',
        'site_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'token',
        'usr_id',
        'session_id',
        'game_id',
        'casino_game_id',
        'casino_category_id',
        'site_id'
    ];

}
