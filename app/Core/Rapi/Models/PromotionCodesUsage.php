<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionCodesUsage extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'usage_id';
    public $timestamps = false;
    protected $table = 'promotion_codes_usage';
    //public $transformer = PriceTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'usr_id',
        'promotion_id',
        'crt_id',
        'status',
        'usage_date',
        'cancellation_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'usage_id',
        'usr_id',
        'promotion_id',
        'crt_id',
        'status',
        'usage_date',
        'cancellation_date',
    ];
}
