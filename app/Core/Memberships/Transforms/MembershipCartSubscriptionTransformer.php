<?php

namespace App\Core\Memberships\Transforms;

use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CartMembershipSubscription",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="Cart Membership Subscription identifier",
 *       example="123321"
 *     ),
 *     @SWG\Property(
 *       property="cart_id",
 *       type="integer",
 *       format="int32",
 *       description="Cart ID",
 *       example="123"
 *     ),
 *     @SWG\Property(
 *       property="membership_id",
 *       type="integer",
 *       format="int32",
 *       description="Membership ID",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="membership_name",
 *       type="string",
 *       description="Membership name",
 *       example="GOLD"
 *     ),
 *     @SWG\Property(
 *       property="tickets_by_draw",
 *       type="number",
 *       description="Tickets by draw",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Membership price",
 *       example="10.00"
 *     ),
 *     @SWG\Property(
 *       property="renewable",
 *       description="Renewable membership subscription",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="bonus_products",
 *       description="Membership bonus product list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/BonusProductDetail"),
 *     ),
 *     @SWG\Property(
 *       property="cart_timestamp",
 *       description="Cart adding timestamp",
 *       type="date_time",
 *       example="2018-11-13 15:13:17"
 *     ),
 *   ),
 */
class MembershipCartSubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($membershipCartSubscription) {
        return [
            'identifier' => (integer) $membershipCartSubscription->cts_id,
            'cart_id' => (integer) $membershipCartSubscription->crt_id,
            'membership_id' => (integer) $membershipCartSubscription->memberships_id,
            'membership_name' => (string) $membershipCartSubscription->membership->tag_name,
            'tickets_by_draw' => 1,
            'price' => $membershipCartSubscription->cts_price,
            'renewable' => $membershipCartSubscription->cts_renew,
            'bonus_products' => $membershipCartSubscription->bonus_products,
            'cart_timestamp' => $membershipCartSubscription->cart_time_stamp,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cts_id',
            'cart_id' => 'crt_id',
            'membership_id' => 'memberships_id',
            'price_line_id' => 'prcln_id',// memberships_prices_line.prcln_id
            'renewable' => 'cts_renew',
            'price_id' => 'cts_prc_id', // memberships_prices.prc_id
            'bonus' => 'bonus_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cts_id' => 'identifier',
            'crt_id' => 'cart_id',
            'memberships_id' => 'membership_id',
            'prcln_id' => 'price_line_id',
            'cts_renew' => 'renewable',
            'cts_prc_id' => 'price_id',
            'bonus_id' => 'bonus',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
