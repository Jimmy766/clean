<?php

namespace App\Core\Lotteries\Transforms;

use App\Core\Base\Services\TranslateTextService;
use App\Core\Lotteries\Models\LotteryModifier;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LotteryModifier",
 *     required={"identifier","description","tag","balls","extra_balls"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Draw identifier",
 *       example="7"
 *     ),
 *     @SWG\Property(
 *       property="description",
 *       type="string",
 *       description="Description of modifier",
 *       example="Straight"
 *     ),
 *     @SWG\Property(
 *       property="tag",
 *       type="string",
 *       description="Tag of modifier",
 *       example="STRAIGHT"
 *     ),
 *     @SWG\Property(
 *       property="balls",
 *       description="Number of common balls modify",
 *       type="integer",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="extra_balls",
 *       description="Number of extra balls modify",
 *       type="integer",
 *       example="0"
 *     ),
 *  ),
 */

class LotteryModifierTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LotteryModifier $lottery_modifier) {
        return [
            'identifier'    => (integer) $lottery_modifier->modifier_id,
            'description'   => $lottery_modifier->modifier_description,
            'tag'           => $lottery_modifier->tag_name,
            'translate_tag' => TranslateTextService::execute($lottery_modifier->tag_name),
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'modifier_id',
            'description' => 'modifier_description',
            'tag' => 'modifier_tag',
            'balls' => 'mod_balls',
            'extra_balls' => 'mod_extra_balls',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'modifier_id' => 'identifier',
            'modifier_description' => 'description',
            'modifier_tag' => 'tag',
            'mod_balls' => 'balls',
            'mod_extra_balls' => 'extra_balls',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
