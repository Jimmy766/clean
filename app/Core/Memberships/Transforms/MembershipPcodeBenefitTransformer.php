<?php

namespace App\Core\Memberships\Transforms;

use App\Core\Memberships\Models\MembershipPcodeBenefit;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="MembershipPcodeBenefit",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="title_tag",
 *       type="string",
 *       description="Title Tag",
 *       example="#MGOLD_MY_BENEF_TEXT_1#"
 *     ),@SWG\Property(
 *       property="description_tag",
 *       type="string",
 *       description="Description Tag",
 *       example="#MGOLD_MY_BENEF_TEXT_1_DESC#"
 *     ),
 *     @SWG\Property(
 *       property="promotion_codes",
 *       description="Promotion Codes",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/PromotionMembershipBenefit")
 *     ),
 *   ),
 */
class MembershipPcodeBenefitTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(MembershipPcodeBenefit $membershipPcodeBenefit) {
        return [
            //'type' => $membershipPcodeBenefit->benefit_type,
            'title_tag' => (string) $membershipPcodeBenefit->title_tag,
            'description_tag' => (string) $membershipPcodeBenefit->description_tag,
            'promotion_codes' =>  $membershipPcodeBenefit->promo_codes,
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
