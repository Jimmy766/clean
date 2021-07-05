<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Lotteries\Models\LiveDraw;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="LiveDraw",
 *     required={"identifier","external_id","date","time"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ID Draw identifier",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="draw_number",
 *       type="integer",
 *       description="ID external identifier",
 *       example="38"
 *     ),
 *     @SWG\Property(
 *       property="date",
 *       type="string",
 *       format="date",
 *       description="Draw date",
 *       example="2018-06-11"
 *     ),
 *     @SWG\Property(
 *       property="time",
 *       type="string",
 *       description="Draw time",
 *       example="23:30:15"
 *     ),
 *     @SWG\Property(
 *       property="time_zone",
 *       type="string",
 *       description="Draw time zone",
 *       example="EDT"
 *     ),
 *     @SWG\Property(
 *       property="video_url",
 *       type="array",
 *       description="Video url",
 *       @SWG\Items(
 *            @SWG\Property(property="defaults", type="string",example="https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/Manifest-1523343615-50.m3u8"),
 *            @SWG\Property(property="ios", type="string", example="https://us.dvr1.amberbox.eu/bs-gen-q3/ntsc/index-1523343615-50.m3u8")
 *       )
 *     ),
 *     @SWG\Property(
 *       property="result",
 *       description="Draw results object or -1",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/ResultDraw"),
 *       }
 *     ),
 *  ),
 */

class LiveDrawTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(LiveDraw $live_draw) {
        return [
            'identifier' => (integer)$live_draw->draw_id,
            'draw_number' => (integer)$live_draw->draw_external_id,
            'date' => $live_draw->draw_date_display,
            'time' => $live_draw->draw_time_display,
            'time_zone' => $live_draw->draw_time_zone_display,
            'video_url' => $live_draw->video_url,
            'results' => $live_draw->results,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'draw_id',
            'date' => 'draw_date',
            'time' => 'draw_time',
            'external_id' => 'draw_external_id',
            'ball1' => 'draw_ball1',
            'ball2' => 'draw_ball2',
            'ball3' => 'draw_ball3',
            'ball4' => 'draw_ball4',
            'ball5' => 'draw_ball5',
            'ball6' => 'draw_ball6',
            'ball7' => 'draw_ball7',
            'ball8' => 'draw_ball8',
            'ball9' => 'draw_ball9',
            'ball10' => 'draw_ball10',
            'ball11' => 'draw_ball11',
            'ball12' => 'draw_ball12',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'draw_id' => 'identifier',
            'draw_date' => 'date',
            'draw_time' => 'time',
            'draw_external_id' => 'external_id',
            'ball1' => 'draw_ball1',
            'ball2' => 'draw_ball2',
            'ball3' => 'draw_ball3',
            'ball4' => 'draw_ball4',
            'ball5' => 'draw_ball5',
            'ball6' => 'draw_ball6',
            'ball7' => 'draw_ball7',
            'ball8' => 'draw_ball8',
            'ball9' => 'draw_ball9',
            'ball10' => 'draw_ball10',
            'ball11' => 'draw_ball11',
            'ball12' => 'draw_ball12',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
