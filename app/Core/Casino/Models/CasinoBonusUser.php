<?php

namespace App\Core\Casino\Models;

use App\Core\Base\Traits\CartUtils;
use App\Core\Casino\Models\CasinoBonus;
use App\Core\Casino\Models\CasinoBonusCategory;
use App\Core\Casino\Transforms\CasinoBonusUserTransformer;
use Illuminate\Database\Eloquent\Model;

class CasinoBonusUser extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    protected $table = 'casino_bonus_user';
    public $transformer = CasinoBonusUserTransformer::class;
    const CREATED_AT = 'reg_date';

    use CartUtils;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'casino_bonus_id',
        'usr_id',
        'initial_amount',
        'initial_wr',
        'amount',
        'wr',
        'curr_code',
        'active',
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
        'initial_amount',
        'initial_wr',
        'amount',
        'wr',
        'curr_code',
        'active',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bonus(){
        return $this->belongsTo(CasinoBonus::class,'casino_bonus_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bonus_category(){
        return $this->hasMany(CasinoBonusCategory::class, 'casino_bonus_id', 'casino_bonus_id');
    }

    /*public function bonus_not_expired(){
        return $this->bonus()->where('expiration_date', '>=', date('Y-m-d h:i:s'));
    }*/


    public function getAmountConvertedAttribute() {
        if ($this->curr_code != request()->user()->curr_code) {
            return $this->convertCurrency($this->curr_code, request()->user()->curr_code) * $this->amount;
        }
        return $this->amount;
    }
}
