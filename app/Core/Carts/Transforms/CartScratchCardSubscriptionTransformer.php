<?php

namespace App\Core\Carts\Transforms;

use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="CartScratchCardSubscription",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="cart",
 *       type="integer",
 *       format="int32",
 *       description="Cart ID",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="scratch_card",
 *       type="integer",
 *       format="int32",
 *       description="Scratch card ID",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Price",
 *       example="0.1"
 *     ),
 *     @SWG\Property(
 *       property="rounds",
 *       type="integer",
 *       description="Rounds",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="rounds_free",
 *       type="integer",
 *       description="Rounds free",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="bonus",
 *       type="integer",
 *       description="Bonus",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="price_attributes",
 *       description="Price attributes",
 *       type="object",
 *       allOf={ @SWG\Schema(ref="#/definitions/ScratchCardPrice"), }
 *     ),
 *     @SWG\Property(
 *       property="cart_timestamp",
 *       description="Cart adding timestamp",
 *       type="date_time",
 *       example="2018-11-13 15:13:17"
 *     ),
 *   ),
 */

class CartScratchCardSubscriptionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($cart_scratch_card_subscription) {
        return [
            'identifier' => (integer)$cart_scratch_card_subscription->cts_id,
            'cart' => (integer)$cart_scratch_card_subscription->crt_id,
            'scratch_card' => (integer)$cart_scratch_card_subscription->scratches_id,
            'name' => $cart_scratch_card_subscription->scratch_card->name,
            'tag_name' => $cart_scratch_card_subscription->scratch_card->name_tag,
            'tag_info' => $cart_scratch_card_subscription->scratch_card->info_tag,
            'scratch_card_prices' => $cart_scratch_card_subscription->scratch_card->prices_list,
            'price' => round((float)$cart_scratch_card_subscription->cts_price,2),
            'rounds' => $cart_scratch_card_subscription->cts_rounds,
            'rounds_free' => $cart_scratch_card_subscription->cts_rounds_free,
            'bonus' => $cart_scratch_card_subscription->bonus_id,
            'price_attributes' => $cart_scratch_card_subscription->price_attributes,
            'cart_timestamp' => $cart_scratch_card_subscription->cart_time_stamp,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cts_id',
            'cart_id' => 'crt_id',
            'scratch_card' => 'scratches_id',
            'price' => 'cts_price',
            'rounds' => 'cts_rounds',
            'rounds_free' => 'cts_rounds_free',
            'bonus' => 'bonus_id',
            'price_id' => 'prc_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cts_id' => 'identifier',
            'cart_id' => 'crt_id',
            'scratches_id' => 'scratch_card',
            'cts_price' => 'price',
            'cts_rounds' => 'rounds',
            'cts_rounds_free' => 'rounds_free',
            'bonus_id' => 'bonus',
            'prc_id' => 'price_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
