<?php

namespace App\Core\Memberships\Transforms;

use App\Core\Rapi\Models\Promotion;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="PromotionMembershipBenefit",
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
 *       property="used",
 *       type="boolean",
 *       description="Promotion used by the user",
 *       example="true"
 *     ),
 *     @SWG\Property(
 *       property="discount_levels",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/DiscountLevels"),
 *
 *     ),
 *   ),
 */
class PromotionMembershipBenefitTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Promotion $promotion) {
        return [
            'code' => (string)$promotion->code,
            'discount_type' => (integer)$promotion->discount_type,
            'used'=> (integer) $promotion->promotion_code_usage,
            'discount_levels' => $promotion->promotion_discount_levels_attributes,
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
