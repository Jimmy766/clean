<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\DealTransformer;
use App\Core\Rapi\Transforms\PromotionListTransformer;
use App\Core\Rapi\Transforms\PromotionTransformer;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $guarded = [];
    public $connection = 'mysql_external';
    public $timestamps = false;
    public $transformer = DealTransformer::class;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'promotion_id',
        'sys_id',
        'deal_type',
        'deal_max_uses',
        'deal_max_uses_type',
        'deal_promo_type',
        'deal_promo_value',
        'deal_level',
        'deal_active',
        'deal_logo',
        'deal_tag',
        'deal_date',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'promotion_id',
        'sys_id',
        'deal_type',
        'deal_max_uses',
        'deal_max_uses_type',
        'deal_promo_type',
        'deal_promo_value',
        'deal_level',
        'deal_active',
        'deal_logo',
        'deal_tag',
        'deal_date',
        'promotion',

    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promotion() {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id')
            ->where('expiration_date', '>', date('Y-m-d'));
    }

    /**
     * @return PromotionTransformer
     */
    public function getPromotionAttributesAttribute() {
        $promotion = $this->promotion;
        if($promotion)
        {
            $promotion->transformer = PromotionListTransformer::class;
            return $promotion->transformer::transform($promotion);
        }
        return $promotion;
    }

    public function getTagAttribute() {
        return ($this->deal_tag != '') ? "#".$this->deal_tag."#" : null;
    }

    public function lottosByPromo(){
        $promotion = $this->promotion;
        if($promotion) {
            return explode(",", $promotion->promo_product_lot_id);

        }
        return $promotion;
    }



}
