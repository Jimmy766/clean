<?php

namespace App\Core\Users\Transforms;

use App\Core\Rapi\Models\ProductType;
use App\Core\Rapi\Models\Ticket;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="UserWinnings",
 *     @SWG\Property(
 *         property="identifier",
 *         type="number",
 *         description="Subscription Identifier",
 *         example="15"
 *       ),
 *       @SWG\Property(
 *         property="product_type_identifier",
 *         type="number",
 *         description="Product Type Identifier",
 *         example="1"
 *       ),
 *       @SWG\Property(
 *         property="product_identifier",
 *         type="number",
 *         description="Product Identifier",
 *         example="8"
 *       ),
 *       @SWG\Property(
 *         property="product_type_name",
 *         type="string",
 *         description="Product Type Name",
 *         example="Lottery"
 *       ),
 *       @SWG\Property(
 *         property="product_name",
 *         type="string",
 *         description="Product Name",
 *         example="EuroMillions"
 *       ),
 *       @SWG\Property(
 *         property="draw_date",
 *         type="string",
 *         format="date-time",
 *         description="Draw Date",
 *         example="2018-02-20",
 *       ),
 *       @SWG\Property(
 *         property="region",
 *         type="string",
 *         description="Product Region",
 *         example="Europe",
 *       ),
 *       @SWG\Property(
 *         property="prize",
 *         type="number",
 *         format="float",
 *         description="Prize",
 *         example="10.3"
 *       ),
 *       @SWG\Property(
 *         property="currency",
 *         type="string",
 *         description="Prize Currency",
 *         example="USD"
 *       ),
 *  )
 */

class UserWinningsLotteryListTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Ticket $ticket) {
        $lottery  = $ticket->subscription->lottery;
        return [
            'identifier' => (int) $lottery->lot_live == 1 ? $ticket->sub_id : $ticket->tck_id,
            'product_type_identifier' => (int) $lottery->lot_live == 1 ? 10 : 1,
            'product_identifier' => (int) $lottery->lot_id,
            'product_name'=> (string) $lottery->name,
            //'draw_date'=> (string) $lottery->lot_live == 1 ? $ticket->draw->live_lottery_draw_date_display : $ticket->draw->draw_date,
            'draw_date'=> (string) $ticket->draw->draw_date,
            'region' => (string) $lottery->lot_live == 1 ? null : $lottery->region_attributes['name'],
            'prize'=> (double) $ticket->tck_prize_usr,
            'currency' => (string) $ticket->curr_code,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
