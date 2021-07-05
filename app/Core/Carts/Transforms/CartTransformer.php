<?php

namespace App\Core\Carts\Transforms;

use App\Core\Carts\Models\Cart;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="Cart",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="123"
 *     ),
 *     @SWG\Property(
 *       property="creation_date",
 *       type="string",
 *       format="date",
 *       description="Creation date",
 *       example="2018-06-11"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Price of cart",
 *       example="1234.5"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="email",
 *       description="Email",
 *       type="string",
 *       example="pp1234@something.com"
 *     ),
 *     @SWG\Property(
 *       property="payway",
 *       description="Payway ID",
 *       type="integer",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="pay_method",
 *       description="Pay method",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       description="Status cart (1 => 'Pending', 2 => 'Processed', 3 => 'Cancel', 4 => 'Pre-authorize')",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="type",
 *       description="Cart type (1=> 2=> 3=>)",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="total",
 *       type="number",
 *       format="float",
 *       description="Total price of cart",
 *       example="1234.5"
 *     ),
 *     @SWG\Property(
 *       property="discount",
 *       type="number",
 *       format="float",
 *       description="Discount price of cart",
 *       example="1234.5"
 *     ),
 *     @SWG\Property(
 *       property="from_account",
 *       type="number",
 *       format="float",
 *       description="Discount from account",
 *       example="1234.5"
 *     ),
 *     @SWG\Property(
 *       property="promotion_code",
 *       type="string",
 *       description="Promotion code",
 *       example="QAZ12345XSW"
 *     ),
 *     @SWG\Property(
 *       property="promotion_points",
 *       type="integer",
 *       description="Promotion points",
 *       example="12"
 *     ),
 *     @SWG\Property(
 *       property="promotion_high_value",
 *       type="integer",
 *       description="Promotion high value",
 *       example="12"
 *     ),
 *     @SWG\Property(
 *       property="promotion_discount_value",
 *       type="integer",
 *       description="Promotion discount value",
 *       example="12"
 *     ),
 *     @SWG\Property(
 *       property="promotion_attributes",
 *       type="object",
 *       description="Promotion",
 *       allOf={ @SWG\Schema(ref="#/definitions/CartPromotionTransformer"), }
 *     ),
 *     @SWG\Property(
 *       property="cart_lotteries_list",
 *       description="Cart Lotteries list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/CartSubscription"),
 *     ),
 *     @SWG\Property(
 *       property="cart_syndicate_list",
 *       description="Cart Syndicates list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/CartSyndicateSubscription"),
 *     ),
 *     @SWG\Property(
 *       property="cart_raffle_list",
 *       description="Cart Raffle list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/CartRaffle"),
 *     ),
 *     @SWG\Property(
 *       property="cart_scratch_card_list",
 *       description="Cart scratch cards list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/CartScratchCardSubscription"),
 *     ),
 *     @SWG\Property(
 *       property="cart_live_list",
 *       description="Cart live lotteries list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/CartLiveLotterySubscription"),
 *     ),
 *     @SWG\Property(
 *       property="cart_membership_list",
 *       description="Cart memberships list",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/CartMembershipSubscription"),
 *     ),
 *   ),
 */
// falta poner los listados de subscriptions

class CartTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart) {
        $return = [
            'identifier' => (integer)$cart->crt_id,
            'creation_date' => (string)$cart->crt_date,
            'price' => $cart->crt_price,
            'currency' => $cart->crt_currency,
            'email' => $cart->crt_email,
            'payway' => $cart->pay_id,
            'pay_method' => $cart->crt_pay_method,
            'status'=>$cart->crt_status,
            'type' => $cart->cart_type,
            'total' => $cart->crt_total,
            'discount' => $cart->crt_discount,
            'from_account'  => $cart->crt_from_account,
            'promotion_code' => $cart->crt_promotion_code,
            'promotion_points' => $cart->crt_promotion_points,
            'promotion_high_value' => $cart->promotion_high_value,
            'promotion_discount_value' => $cart->promotion_discount_value,
            'promotion_attributes' => $cart->promotion_attributes,
            'cart_lottery_list' => $cart->cart_subscriptions_list_attributes,
            'cart_syndicate_list' => $cart->syndicate_cart_subscriptions_list_attributes,
            'cart_raffle_list' => $cart->cart_raffles_list_attributes,
            'cart_raffle_syndicate_list' => $cart->cart_raffles_syndicate_list_attributes,
            'cart_scratch_card_list' => $cart->cart_scratch_card_subscriptions_list_attributes,
            'cart_live_list' => $cart->cart_live_subscriptions_list_attributes,
            'cart_membership_list' => $cart->cart_membership_subscriptions_list_attributes,
        ];
        if (isset($cart->total_products)) {
            $return['total_products'] = $cart->total_products;
        }
        if (isset($cart->deleted_products)) {
            $return['deleted_products'] = $cart->deleted_products;
        }
        return $return;
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'crt_id',
            'creation_date' => 'crt_date',
            'price' => 'crt_price',
            'currency' => 'crt_currency',
            'email' => 'crt_email',
            'payway' => 'pay_id',
            'status'=> 'crt_status',
            'type' => 'cart_type',
            'total' => 'crt_total',
            'discount' => 'crt_discount',
            'from_account'  => 'crt_from_account',
            'promotion_code' => 'crt_promotion_code',
            'promotion_points' => 'crt_promotion_points',
            'pay_method' => 'crt_pay_method',
            'affiliate_cookie' => 'crt_affcookie',
            'track' => 'crt_track',
            'utm_source' => 'utm_source',
            'utm_campaign' => 'utm_campaign',
            'utm_medium' => 'utm_medium',
            'utm_content' => 'utm_content',
            'utm_term' => 'utm_term',
            'use_vip_points' => 'use_vip_points'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'crt_id' => 'identifier',
            'crt_date' => 'creation_date',
            'crt_price' => 'price',
            'crt_currency' => 'currency',
            'crt_email' => 'email',
            'pay_id' => 'payway',
            'crt_status'=> 'status',
            'cart_type' => 'type',
            'crt_total' => 'total',
            'crt_discount' => 'discount',
            'crt_from_account'  => 'from_account',
            'crt_promotion_code' => 'promotion_code',
            'crt_promotion_points' => 'promotion_points',
            'crt_pay_method' => 'pay_method',
            'crt_affcookie' => 'affiliate_cookie',
            'crt_track' => 'track',
            'utm_source' => 'utm_source',
            'utm_campaign' => 'utm_campaign',
            'utm_medium' => 'utm_medium',
            'utm_content' => 'utm_content',
            'utm_term' => 'utm_term',
            'use_vip_points' => 'use_vip_points'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
