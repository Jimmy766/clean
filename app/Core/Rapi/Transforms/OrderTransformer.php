<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Order;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Order",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Order identifier",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date-time",
 *       description="Order date",
 *       example="2010-03-27 16:40:22",
 *     ),
 *     @SWG\Property(
 *       property="payment_method",
 *       type="string",
 *       description="Order payment method",
 *       example="PAY_METHOD_BONUS",
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Order currency",
 *       example="USD",
 *     ),
 *     @SWG\Property(
 *       property="amount",
 *       type="number",
 *       format="float",
 *       description="Order amount",
 *       example="13.45",
 *     ),
 *     @SWG\Property(
 *       property="discount",
 *       type="number",
 *       format="float",
 *       description="Order amount discount",
 *       example="1.45",
 *     ),
 *     @SWG\Property(
 *       property="from_account",
 *       type="number",
 *       format="float",
 *       description="Order amount from account",
 *       example="10.0",
 *     ),
 *     @SWG\Property(
 *       property="price",
 *       type="number",
 *       format="float",
 *       description="Order cash price",
 *       example="2.0",
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="Payment status",
 *       example="ORDERS_DETAIL_CONFIRMED",
 *     ),
 *     @SWG\Property(
 *       property="lotteries",
 *       type="array",
 *       description="Order lotteries subscriptions",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="identifier",
 *           type="integer",
 *           description="Subscription identifier",
 *           example="1234",
 *         ),
 *         @SWG\Property(
 *           property="lottery_identifier",
 *           type="integer",
 *           description="Lottery identifier",
 *           example="13",
 *         ),
 *         @SWG\Property(
 *           property="name",
 *           type="string",
 *           description="Lottery name",
 *           example="Australia OZ Lotto",
 *         ),
 *         @SWG\Property(
 *           property="wheel",
 *           type="object",
 *           description="Wheel",
 *           allOf={ @SWG\Schema(ref="#/definitions/Wheel"  )}
 *         ),
 *         @SWG\Property(
 *           property="subscriptions",
 *           type="integer",
 *           description="Lottery subscriptions quantity",
 *           example="1",
 *         ),
 *         @SWG\Property(
 *           property="duration",
 *           type="string",
 *           description="Lottery subscriptions duration",
 *           example="1 Month",
 *         ),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="lottery_syndicates",
 *       type="array",
 *       description="Order lottery syndicate subscriptions",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="identifier",
 *           type="integer",
 *           description="Subscription identifier",
 *           example="1234",
 *         ),
 *         @SWG\Property(
 *           property="name",
 *           type="string",
 *           description="Syndicate name",
 *           example="MegaMillions Syndicate",
 *         ),
 *         @SWG\Property(
 *           property="tag_name",
 *           type="string",
 *           description="Syndicate tag name",
 *           example="#PLAY_GROUP_NAME_GROUP_MEGAMILLIONS#",
 *         ),
 *         @SWG\Property(
 *           property="tag_name_short",
 *           type="string",
 *           description="Syndicate tag name short",
 *           example="#PLAY_GROUP_NAME_SHORT_GROUP_MEGAMILLIONS#",
 *         ),
 *         @SWG\Property(
 *           property="subscriptions",
 *           type="integer",
 *           description="Syndicate subscriptions quantity",
 *           example="1",
 *         ),
 *         @SWG\Property(
 *           property="duration",
 *           type="string",
 *           description="Syndicate subscriptions duration",
 *           example="1 #MONTH#",
 *         ),
 *         @SWG\Property(
 *           property="games",
 *           type="integer",
 *           description="Syndicate subscriptions games quantity",
 *           example="33",
 *         ),
 *         @SWG\Property(
 *           property="free_tickets",
 *           type="integer",
 *           description="Syndicate subscriptions games quantity",
 *           example="1",
 *         ),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="raffles",
 *       type="array",
 *       description="Order raffles subscriptions",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="identifier",
 *           type="integer",
 *           description="Subscription identifier",
 *           example="1234",
 *         ),
 *         @SWG\Property(
 *           property="name",
 *           type="string",
 *           description="Raffle name",
 *           example="#SPAIN_THURSDAY#",
 *         ),
 *         @SWG\Property(
 *           property="tickets",
 *           type="integer",
 *           description="Raffle subscriptions ticket quantity",
 *           example="1",
 *         ),
 *         @SWG\Property(
 *           property="tickets_tag",
 *           type="string",
 *           description="Raffle subscriptions ticket quantity tag",
 *           example="#GORDO_RAFFLE_DEC#",
 *         ),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="raffle_syndicates",
 *       type="array",
 *       description="Order raffle syndicate subscriptions",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="identifier",
 *           type="integer",
 *           description="Subscription identifier",
 *           example="1234",
 *         ),
 *         @SWG\Property(
 *           property="name",
 *           type="string",
 *           description="Raffle Syndicate name",
 *           example="#GROUP_SUMMER_GORDO#",
 *         ),
 *         @SWG\Property(
 *           property="subscriptions",
 *           type="integer",
 *           description="Raffle Syndicate subscriptions quantity",
 *           example="1",
 *         ),
 *         @SWG\Property(
 *           property="duration",
 *           type="string",
 *           description="Raffle Syndicate subscriptions duration",
 *           example="1 #MONTH#",
 *         ),
 *         @SWG\Property(
 *           property="games",
 *           type="integer",
 *           description="Raffle Syndicate subscriptions games quantity",
 *           example="33",
 *         ),
 *         @SWG\Property(
 *           property="free_tickets",
 *           type="integer",
 *           description="Raffle Syndicate subscriptions games quantity",
 *           example="1",
 *         ),
 *       ),
 *     ),
 *     @SWG\Property(
 *       property="scratches",
 *       type="array",
 *       description="Order scratches subscriptions",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="identifier",
 *           type="integer",
 *           description="Subscription identifier",
 *           example="1234",
 *         ),
 *         @SWG\Property(
 *           property="name",
 *           type="string",
 *           description="Scratch name",
 *           example="#THE_ALCHEMIST_NAME#",
 *         ),
 *         @SWG\Property(
 *           property="games",
 *           type="integer",
 *           description="Scratch subscriptions games quantity",
 *           example="5",
 *         ),
 *       ),
 *     ),
 *  )
 */

class OrderTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Order $order) {
        return [
            'identifier' => (integer)$order->crt_id,
            'date' => $order->date,
            'payment_method' => $order->payment_method,
            'currency' => $order->crt_currency,
            'amount' => $order->order_amount,
            'discount' => $order->crt_discount,
            'from_account' => $order->crt_from_account,
            'price' => $order->crt_price,
            'status' => $order->status,
            'lotteries' => $order->lotteries_subscriptions,
            'lotteries_extra_info' => $order->cart_subscriptions_list_attributes,
            'lottery_syndicates' => $order->syndicate_subscriptions,
            'raffles' => $order->raffles_subscriptions,
            'raffle_syndicates' => $order->raffles_syndicate_subscriptions,
            'scratches' => $order->scratches_subscriptions,
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
