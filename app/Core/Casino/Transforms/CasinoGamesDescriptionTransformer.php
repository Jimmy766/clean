<?php

namespace App\Core\Casino\Transforms;

use App\Core\Casino\Models\CasinoGamesDescription;
use App\Core\Base\Traits\UtilsFormatText;
use League\Fractal\TransformerAbstract;
use Swagger\Annotations as SWG;

/**
 *   @SWG\Definition(
 *     definition="CasinoGamesDescription",
 *     required={"name","description","text"},
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of the game",
 *       example="Blackjack"
 *     ),
 *     @SWG\Property(
 *       property="description",
 *       type="string",
 *       description="Short description of the game in html",
 *       example="The objective of the Blackjack is ..."
 *     ),
 *     @SWG\Property(
 *       property="text",
 *       type="string",
 *       description="Long description of the game in html",
 *       example="Both the dealer and the player are dealt two cards per hand..."
 *     ),
 *   )
 */

class CasinoGamesDescriptionTransformer extends TransformerAbstract
{
    use UtilsFormatText;
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(CasinoGamesDescription $casinoGamesDescription) {


        return [
            'name'        => (new CasinoGamesDescriptionTransformer)->convertTextCharset($casinoGamesDescription->name),
            'description' => (new CasinoGamesDescriptionTransformer)->convertTextCharset($casinoGamesDescription->description),
            'text'        => (new CasinoGamesDescriptionTransformer)->convertTextCharset($casinoGamesDescription->how_to_win),
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'name' => 'name',
            'description' => 'description',
            'text' => 'how_to_win'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'name' => 'name',
            'description' => 'description',
            'description' => 'text'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
