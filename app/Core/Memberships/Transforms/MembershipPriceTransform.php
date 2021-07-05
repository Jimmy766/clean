<?php

namespace App\Core\Memberships\Transforms;

use App\Core\Memberships\Models\MembershipPrice;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="MembershipPrice",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Draw identifier",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="price_line",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/MembershipPriceLine"),
 *       }
 *     ),
 *  ),
 */

class MembershipPriceTransform extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(MembershipPrice $membership_price) {
        return [
            'identifier' => (integer)$membership_price->prc_id,
            'price_line' => $membership_price->price_line,
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
