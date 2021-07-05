<?php

namespace App\Core\Memberships\Transforms;

use App\Core\Memberships\Models\MembershipPriceLine;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="MembershipPriceLine",
 *     required={"identifier","price","free_credit","currency"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Draw identifier",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Price of Membership",
 *       example="10"
 *     ),
 *     @SWG\Property(
 *       property="free_credit",
 *       type="string",
 *       description="Free credit of Membership",
 *       example="10"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *  ),
 */

class MembershipPriceLineTransform extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(MembershipPriceLine $membership_price_line) {
        return [
            'identifier' => (integer)$membership_price_line->prcln_id,
            'price' => (string)$membership_price_line->prcln_price,
            'free_credit' => (string)$membership_price_line->prc_free_credit,
            'currency' => (string)$membership_price_line->curr_code,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'prc_id',
            'active' => 'active',

        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'prc_id' => 'identifier',
            'active' => 'active',
        ];
        return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
    }
}
