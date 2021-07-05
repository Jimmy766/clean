<?php

namespace App\Core\Rapi\Models;

use App\Core\Bonus\Models\BonusProduct;
use App\Core\Rapi\Transforms\BonusProductDetailTransformer;
use App\Core\Rapi\Transforms\BonusTransformer;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $guarded=[];
    public $connection = 'mysql_external';
    public $timestamps = false;
    public $transformer = BonusTransformer::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source',
        'description',
        'active',
        'reg_date',
        'site_id',
        'sys_id',
    ];

    /**
     * The attributes that should be visible for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id',
        'source',
        'description',
        'active',
        'reg_date',
        'site_id',
        'sys_id',
    ];

    public function products() {
        return $this->hasMany(BonusProduct::class, 'bonus_id', 'id')
            ->where('active', '=', 1);
    }

    public function getBonusProductsAttribute() {
        $bonus_products = collect([]);
        $this->products->each(function ($item, $key) use ($bonus_products) {
            $bonus_products->push($item->transformer::transform($item));
        });
        return $bonus_products;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getBonusProductsDetailAttribute() {
        $bonus_products = collect([]);
        $this->bonus_price_total = 0;
        $this->products->each(function ($item, $key) use ($bonus_products) {
            $item->product_detail;
            $this->bonus_price_total += $item->product_price['price'];
            $item->transformer = BonusProductDetailTransformer::class;
            $bonus_products->push($item->transformer::transform($item));
        });
        return $bonus_products;
    }

}
