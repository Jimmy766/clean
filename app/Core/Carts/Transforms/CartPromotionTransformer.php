<?php


namespace App\Core\Carts\Transforms;


class CartPromotionTransformer
{
    /**
     *   @SWG\Definition(
     *     definition="CartPromotionTransformer",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       description="Promotion name",
     *       example="MM Lottery - 10%OFF"
     *     ),
     *     @SWG\Property(
     *       property="description",
     *       type="string",
     *       description="Promotion description",
     *       example=""
     *     ),
     *     @SWG\Property(
     *       property="code",
     *       type="string",
     *       description="Promotion Code",
     *       example="WO25MJOW"
     *     ),
     *     @SWG\Property(
     *       property="discount_type",
     *       type="integer",
     *       description="Discount type to apply",
     *       example="2"
     *     ),
     *     @SWG\Property(
     *       property="user_type",
     *       type="integer",
     *       description="User type",
     *       example="1"
     *     ),
     *     @SWG\Property(
     *       property="discount_levels",
     *       type="array",
     *       @SWG\Items(ref="#/definitions/DiscountLevels"),
     *
     *     ),
     *     @SWG\Property(
     *       property="tag",
     *       type="string",
     *       description="Promo Tag",
     *       example="#CART_STEP1_PROMO_MATCH_BONUS#"
     *     ),
     *     @SWG\Property(
     *       property="extra_messages",
     *       description="Promotion extra message",
     *       type="string",
     *       example="Finish your order now and receive a 10% refund - up to US$10"
     *     ),
     *   ),
     */

}
