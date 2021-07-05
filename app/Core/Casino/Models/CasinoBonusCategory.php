<?php

namespace App\Core\Casino\Models;

use App\Core\Casino\Models\CasinoCategory;
use Illuminate\Database\Eloquent\Model;

class CasinoBonusCategory extends Model
{

    protected $guarded = [];
    public $connection = 'mysql_external';
    protected $table = 'casino_bonus_category';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contribution_percent',
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
        'contribution_percent',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function casino_category(){
        return $this->belongsTo(CasinoCategory::class,'casino_category_id','id');
    }
}
