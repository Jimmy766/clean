<?php

namespace App\Core\Telem\Transforms;

use App\Core\Syndicates\Models\Syndicate;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="TelemSyndicateWheelCart",
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
 *  ),
 */

class TelemSyndicateWheelCartTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Syndicate $syndicate) {
        return [
            'identifier' => (integer)$syndicate->id,
            'name' => $syndicate->printable_name,
            'has_wheel' => $syndicate->has_wheel,
            "wheel_info" => $syndicate->wheel_info,
            'tag_name' => '#PLAY_GROUP_NAME_'.$syndicate->name.'#',
            'tag_name_short' => '#PLAY_GROUP_NAME_SHORT_'.$syndicate->name.'#',
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
