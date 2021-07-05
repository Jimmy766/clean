<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Promotion;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="PromotionCode",
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
class PromotionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Promotion $promotion) {
        return [
            'name' => (string)$promotion->name,
            'description' => (string)$promotion->promo_description,
            'code' => (string)$promotion->code,
            'discount_type' => (integer)$promotion->discount_type,
            'user_type' => (integer)$promotion->user_type,
            'discount_levels' => $promotion->promotion_discount_levels_attributes,
            'tag' => $promotion->tag,
            'extra_message' => $promotion->extra_message_by_lang,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'promotion_id',
            'name' => 'name',
            'description' => 'promo_description',
            'code' => 'code',
            'discount_type' => 'discount_type',
            'user_type' => 'user_type',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'promotion_id' => 'identifier',
            'name' => 'name',
            'promo_description' => 'description',
            'code' => 'code',
            'discount_type' => 'discount_type',
            'user_type' => 'user_type',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
