<?php

namespace App\Core\Syndicates\Transforms;

use League\Fractal\TransformerAbstract;
/**
 *   @SWG\Definition(
 *     definition="CartSyndicateSubscription2",
 *     required={"identifier"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="order",
 *       type="integer",
 *       format="int32",
 *       description="Cart ID",
 *       example="305"
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       description="Subscription status",
 *       type="string",
 *       example="active"
 *     ),
 *     @SWG\Property(
 *       property="status_tag",
 *       description="Subscription status tag",
 *       type="string",
 *       example="#SUBSCRIPTION_DETAIL_STATUS_ACTIVE#"
 *     ),
 *     @SWG\Property(
 *       property="syndicate_identifier",
 *       type="integer",
 *       description="Syndicate Id",
 *       example="111"
 *     ),
 *     @SWG\Property(
 *       property="syndicate_name",
 *       type="string",
 *       description="Syndicate name",
 *       example="EuroMillions Star Syndicate"
 *     ),
 *     @SWG\Property(
 *       property="syndicate_tag_name",
 *       type="string",
 *       description="Syndicate tag name",
 *       example="#PLAY_GROUP_NAME_EUROMILLIONS_STAR#"
 *     ),
 *     @SWG\Property(
 *       property="syndicate_tag_name_short",
 *       type="string",
 *       description="Syndicate tag name short",
 *       example="#PLAY_GROUP_NAME_SHORT_EUROMILLIONS_STAR#"
 *     ),
 *     @SWG\Property(
 *       property="purchase_date",
 *       description="Purchase date",
 *       type="string",
 *       format="date-time",
 *       example="2014-06-05 09:57:55"
 *     ),
 *     @SWG\Property(
 *       property="subscriptions",
 *       description="Subscriptions quantity",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="draws",
 *       description="Draws participation quantity",
 *       type="integer",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="prizes",
 *       description="Prizes",
 *       type="array",
 *       @SWG\Items(
 *         @SWG\Property(property="currency", type="string", description="Currency", example="USD"),
 *         @SWG\Property(property="prize", type="number", format="float", description="Price", example="0.11"),
 *       ),
 *     ),
 *   ),
 */

class SyndicateCartSubscriptionTransformer2 extends TransformerAbstract
{

    /**
     * @param $syndicate_cart_subscription
     * @return array
     */

    public static function transform($syndicate_cart_subscription) {
        $syndicate = $syndicate_cart_subscription->syndicate ? $syndicate_cart_subscription->syndicate : null;
        return [
            'identifier' => $syndicate_cart_subscription->cts_id,
            'order' => $syndicate_cart_subscription->crt_id,
            'status' => $syndicate_cart_subscription->status,
            'status_tag' => $syndicate_cart_subscription->status_tag,
            'syndicate_identifier' => $syndicate_cart_subscription->syndicate_id,
            'syndicate_name' => $syndicate ? $syndicate->printable_name : null,
            'syndicate_draw_date' => $syndicate ? $syndicate->draw_date : null,
            'syndicate_tag_name' => $syndicate ? '#PLAY_GROUP_NAME_'.$syndicate->name.'#' : null,
            'syndicate_tag_name_short' => $syndicate ? '#PLAY_GROUP_NAME_SHORT_'.$syndicate->name.'#' : null,
            'purchase_date' => $syndicate_cart_subscription->purchase_date,
            'subscriptions' => $syndicate_cart_subscription->subscriptions,
            'draws' => $syndicate_cart_subscription->draws,
            'prizes' => $syndicate_cart_subscription->prizes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'cts_id',
            'order' => 'crt_id',
            'status' => 'status',
            'syndicate_identifier' => 'syndicate_id',
            'syndicate_name' => 'syndicate_name',
            'purchase_date' => 'purchase_date',
            'subscriptions' => 'subscriptions',
            'draws' => 'draws',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'cts_id' => 'identifier',
            'crt_id' => 'order',
            'status' => 'status',
            'syndicate_id' => 'syndicate_identifier',
            'syndicate_name' => 'syndicate_name',
            'purchase_date' => 'purchase_date',
            'subscriptions' => 'subscriptions',
            'draws' => 'draws',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
