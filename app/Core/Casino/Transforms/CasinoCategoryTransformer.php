<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoCategory;
use App\Core\Base\Traits\UtilsFormatText;
use League\Fractal\TransformerAbstract;

/**
 *   @SWG\Definition(
 *     definition="CasinoCategory",
 *     required={"identifier","name","games"},
 *     @SWG\Property(
 *       property="category_name",
 *       type="string",
 *       description="Name of Category",
 *       example="Slots"
 *     ),
 *     @SWG\Property(
 *       property="category_tag",
 *       type="string",
 *       description="Tag of Category",
 *       example="#CASINO_SLOT_CATEGORY#"
 *     ),
 *     @SWG\Property(
 *       property="games",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/CasinoGamesCategory"),
 *       }
 *     ),
 *   )
 *
 */

class CasinoCategoryTransformer extends TransformerAbstract
{
    use UtilsFormatText;

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoCategory $casinoCategory){
        return [
            'identifier'    => (integer) $casinoCategory->id,
            'category_name' => (new self())->convertTextCharset($casinoCategory->name),
            'category_tag'  => "#" . (new self())->convertTextCharset($casinoCategory->tag_name) . "#",
            'games'         => $casinoCategory->casino_games_category_attributes,
        ];
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'name' => 'name',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'name' => 'name',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
