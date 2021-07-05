<?php

namespace App\Core\Rapi\Models;

use Illuminate\Database\Eloquent\Model;

/**
 *   @SWG\Definition(
 *     definition="DiscountLevels",
 *     @SWG\Property(
 *       property="up_to",
 *       type="string",
 *       description="Limit value to apply discount value",
 *       example="10"
 *     ),
 *     @SWG\Property(
 *       property="discount_value",
 *       type="string",
 *       description="Value to discount",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="curr_code",
 *       type="string",
 *       description="Discount Level currency",
 *       example="USD"
 *     ),
 *   ),
 */
class PromotionDiscountLevel extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    protected $primaryKey = 'discount_id';
    public $timestamps = false;
    //public $transformer = PriceTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'promotion_id',
        'high_value',
        'discount_value',
        'curr_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'discount_id',
        'promotion_id',
        'high_value',
        'discount_value',
        'curr_code',
    ];
}
