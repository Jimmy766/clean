<?php

namespace App\Core\Memberships\Transforms;

use App\Core\Memberships\Models\MembershipExclusiveProduct;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="MembershipExclusiveProduct",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Membership Exclusive Product Id",
 *       example="1"
 *     ),@SWG\Property(
 *       property="product_type",
 *       type="integer",
 *       description="Product type",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="ticket_by_draw",
 *       type="integer",
 *       description="Ticket by draw",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="bonus_id",
 *       type="integer",
 *       description="Bonus Id",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="product",
 *       description="Membership Exclusive Product detail",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/MembershipExclusiveProductSyndicate"),
 *     ),
 *     @SWG\Property(
 *       property="bonus_products",
 *       description="Bonus product list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/BonusProductDetail"),
 *     ),
 *     @SWG\Property(
 *       property="bonus_price_total",
 *       type="integer",
 *       description="Bonus price total",
 *       example="38"
 *     ),
 *   ),
 */
class MembershipExclusiveProductTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(MembershipExclusiveProduct $membershipExclusiveProduct) {
        return [
            'identifier' => $membershipExclusiveProduct->id,
            'product_type' => $membershipExclusiveProduct->product_type,
            'ticket_by_draw' => $membershipExclusiveProduct->ticket_byDraw,
            'promo_code'=> $membershipExclusiveProduct->pcode,
            'bonus_id' => $membershipExclusiveProduct->bonus_id,
            'product' => $membershipExclusiveProduct->product,
            'bonus_products' => $membershipExclusiveProduct->bonus_products,
            'bonus_price_total' => $membershipExclusiveProduct->bonus_price_total,
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
