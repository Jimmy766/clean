<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Bonus\Models\BonusProduct;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="BonusProductDetail",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="product_type",
 *       type="integer",
 *       description="Product type",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="bonus_product_tag",
 *       type="string",
 *       description="Bonus product description tag",
 *       example="#EXCLUSIVE_PROD_BONUSES_1#"
 *     ),
 *     @SWG\Property(
 *       property="product_name",
 *       type="string",
 *       description="Product name",
 *       example="Syndicate"
 *     ),
 *     @SWG\Property(
 *       property="tickets_by_draw",
 *       type="integer",
 *       description="Tickets by draw",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="price_tag",
 *       type="string",
 *       description="Bonus price tag",
 *       example="#FREE#"
 *     ),
 *     @SWG\Property(
 *       property="product_price",
 *       description="Bonus product price detail",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/SyndicatePrice"),
 *     ),
 *   ),
 */
class BonusProductDetailTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(BonusProduct $bonus_product) {
        return [
            'product_type' => $bonus_product->product_type,
            'bonus_product_tag' => '#'.$bonus_product->bonus_tag.'#',
            'product_name' => $bonus_product->product_name,
            'tickets_by_draw' => 1,
            'price_tag' => '#MEMBERSHIPS_FREE#',
            'product_price'=> $bonus_product->product_price ? : null,

        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
