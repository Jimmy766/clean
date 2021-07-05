<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Models\CasinoGamesBonusUser;
use App\Core\Casino\Models\CasinoGamesBonus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CasinoGamesBonusCategory extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $table = 'casino_bonus_category';
    public $timestamps = false;
    const CREATED_AT = 'reg_date';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'casino_bonus_id',
        'casino_category_id',
        'contribution_percent'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'casino_bonus_id',
        'casino_category_id',
        'contribution_percent'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function casino_games_bonus() {
        return $this->belongsTo(CasinoGamesBonus::class, 'casino_bonus_id', 'id')->where('expiration_date','>=','now()');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function casino_games_bonus_user() {
        return $this->hasMany(CasinoGamesBonusUser::class, 'casino_bonus_id', 'casino_bonus_id')->where('usr_id','=',Auth::user()->usr_id)->whereIn('status',[1,2]);
    }

}
