<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Models\CasinoBonusCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CasinoBonus extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $table = 'casino_bonus';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'promotion_id',
        'expiration_date',
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
        'reg_date',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bonus_category(){
        return $this->hasMany(CasinoBonusCategory::class, 'casino_bonus_id', 'id');
    }
}
