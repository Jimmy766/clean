<?php

namespace App\Core\Rapi\Models;

use App\Core\Rapi\Transforms\ProductTypeTransformer;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $guarded=[];
    public $transformer = ProductTypeTransformer::class;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'name', 'product_table_name', 'product_prices_table_name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $visible = [
        'id', 'type', 'name', 'product_table_name', 'product_prices_table_name'
    ];

    public function getProductAttribute() {
        return new $this->product_table_name;
    }
}
