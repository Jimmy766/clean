<?php

namespace App\Core\Syndicates\Transforms;

use App\Core\AdminLang\Services\AL;
use App\Core\Syndicates\Models\Syndicate;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Syndicate",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Syndicate identifier",
 *       example="123"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of syndicate",
 *       example="Euro Syndi"
 *     ),
 *     @SWG\Property(
 *       property="tag_name",
 *       type="string",
 *       description="Tag Name of syndicate",
 *       example="#PLAY_GROUP_NAME_GROUP_EUROMILLIONS#"
 *     ),
 *     @SWG\Property(
 *       property="tag_name_short",
 *       type="string",
 *       description="Tag Name Short of syndicate",
 *       example="#PLAY_GROUP_NAME_SHORT_GROUP_EUROMILLIONS#"
 *     ),
 *     @SWG\Property(
 *       property="participations_qty",
 *       type="integer",
 *       description="Participation quantity",
 *       example="5"
 *     ),
 *     @SWG\Property(
 *       property="tickets_qty",
 *       type="integer",
 *       description="Tickets quantity",
 *       example="100"
 *     ),
 *     @SWG\Property(
 *       property="multi_lotto",
 *       type="integer",
 *       description="Is multi lotto",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="jackpot",
 *       type="integer",
 *       description="Jackpot",
 *       example="17000000"
 *     ),
 *     @SWG\Property(
 *       property="jackpot_in_usd",
 *       type="integer",
 *       description="Jackpot in USD",
 *       example="19130780"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       type="string",
 *       description="Currency",
 *       example="EUR"
 *     ),
 *     @SWG\Property(
 *       property="draw_date",
 *       description="Next draw date",
 *       type="string",
 *       format="date-time",
 *       example="2018-01-01 12:00:00",
 *     ),
 *     @SWG\Property(
 *       property="prices_list",
 *       description="Syndicate prices",
 *       type="array",
 *       @SWG\Items(ref="#/definitions/SyndicatePrice"),
 *     ),
 *  ),
 */

class SyndicateTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Syndicate $syndicate) {
        return [
            'identifier' => (integer)$syndicate->id,
            'printable_name' => $syndicate->printable_name,
            'name' => AL::translate("PLAY_GROUP_NAME_".$syndicate->name),
            'tag_name' => '#PLAY_GROUP_NAME_'.$syndicate->name.'#',
            'tag_name_short' => '#PLAY_GROUP_NAME_SHORT_'.$syndicate->name.'#',
            'participations_qty' => (integer)$syndicate->participations,
            'tickets_qty' => (integer)$syndicate->chances_to_win,
            'multi_lotto' => (integer)$syndicate->multi_lotto,
            'renewable' => (integer)$syndicate->no_renew ? false : true,
            'renew' => (integer)$syndicate->no_renew ? 0 : 1,
            //'no_renewable' => (integer)$syndicate->no_renew,
            //'pick_type' => (integer)$syndicate->syndicate_pck_type,  LTK
            'jackpot' => (integer)$syndicate->original_jackpot,
            'jackpot_in_usd' => (integer)$syndicate->jackpot,
            'currency' => (string)$syndicate->curr_code,
            'draw_date' => (string)$syndicate->draw_date,
            'prices' => $syndicate->prices_list,
            'routing_friendly' => $syndicate->routing_friendly_attributes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'participations_qty' => 'participations',
            'tickets_qty' => 'tickets_to_show',
            'name' => 'name',
            'multi_lotto' => 'multi_lotto',
            'no_renewable' => 'no_renew',
            'pick_type' => 'syndicate_pck_type',
            'lotteries' => 'lotteries_attributes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'participations' => 'participations_qty',
            'tickets_to_show' => 'tickets_qty',
            'name' => 'name',
            'multi_lotto' => 'multi_lotto',
            'no_renew' => 'no_renewable',
            'syndicate_pck_type' => 'pick_type',
            'lotteries_attributes' => 'lotteries',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
