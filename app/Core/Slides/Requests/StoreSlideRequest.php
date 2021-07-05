<?php

namespace App\Core\Slides\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Swagger\Annotations as SWG;

class StoreSlideRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @SWG\Definition(
     *     definition="StoreSlide",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="slide name"
     *     ),
     *     @SWG\Property(
     *       property="jack_pot",
     *       type="string",
     *       example="2"
     *     ),
     *     @SWG\Property(
     *       property="status",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type_slide",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type_range_program",
     *       type="integer",
     *       example=2
     *     ),
     *     @SWG\Property(
     *       property="type_current_program",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="period_current_program",
     *       type="integer",
     *       example=0
     *     ),
     *     @SWG\Property(
     *       property="regions",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSlideRegionRapi"),
     *          }
     *        )
     *     ),
     *     @SWG\Property(
     *       property="dates",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSlideDates"),
     *          }
     *        )
     *     ),
     *     @SWG\Property(
     *       property="config",
     *       type="array",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSlideConfig"),
     *          }
     *        )
     *     ),
     *     @SWG\Property(
     *       property="images",
     *       type="array",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSlideImages"),
     *          }
     *       )
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSlideRegionRapi",
     *     @SWG\Property(
     *       property="id_region",
     *       type="integer",
     *       example=1
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSlideDates",
     *     @SWG\Property(
     *       property="date_init",
     *       type="date-time",
     *       example="2020-08-05"
     *     ),
     *     @SWG\Property(
     *       property="date_end",
     *       type="date-time",
     *       example="2020-08-06"
     *     ),
     *     @SWG\Property(
     *       property="day_init",
     *       type="date-time",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="day_end",
     *       type="date-time",
     *       example=5
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSlideConfig",
     *     @SWG\Property(
     *       property="title",
     *       type="string",
     *       example="config slide language english"
     *     ),
     *     @SWG\Property(
     *       property="subtitle",
     *       type="string",
     *       example="subtitle config slide language english"
     *     ),
     *     @SWG\Property(
     *       property="description",
     *       type="string",
     *       example="description config language english"
     *     ),
     *     @SWG\Property(
     *       property="text_promotion",
     *       type="string",
     *       example="01-06-2020"
     *     ),
     *     @SWG\Property(
     *       property="url",
     *       type="string",
     *       example="https://www.wintrillions.com/"
     *     ),
     *     @SWG\Property(
     *       property="id_language",
     *       type="integer",
     *       example=1
     *     ),
     *  ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSlideImages",
     *     @SWG\Property(
     *       property="image",
     *       type="string",
     *       example="https://rapi-reports-stage-public.s3.eu-central-1.amazonaws.com/slides/dashboard1599062424.png"
     *     ),
     *     @SWG\Property(
     *       property="id_asset",
     *       type="integer",
     *       example=1
     *     ),
     *  ),
     */

    public function rules()
    {
        return [

            'name'         => 'required|string|min:0|max:150',
            'jack_pot'     => 'nullable|string|min:0|max:150',
            'play_game_at' => 'nullable|date_format:Y-m-d',
            'type_slide'   => 'required|numeric|min:0|max:1',
            'status'       => 'required|numeric|min:0|max:1',
            'active'       => 'required|numeric|min:0|max:1',

            'regions'             => 'required|array|min:1',
            'regions.*'           => 'required',
            'regions.*.id_region' => 'required|integer|exists:regions,id_region',

            'config'               => 'required|array|min:1',
            'config.*'             => 'required',
            'config.*.title'          => 'required|string|min:1|max:150',
            'config.*.subtitle'       => 'nullable|string|min:0|max:150',
            'config.*.text_promotion' => 'nullable|string|min:1|max:150',
            'config.*.description' => 'nullable|string|min:0|max:4294967295',
            'config.*.url'         => 'required|string|min:1|max:250',
            'config.*.id_language' => 'required|integer|exists:languages,id_language',

            'type_range_program'     => 'required|numeric|in:0,1,2',
            'type_current_program'   => 'required_if:type_range_program,1,2|numeric|in:0,1',
            'period_current_program' => 'required_if:type_range_program,1,2|numeric|in:0,1',

            'dates'             => 'required_if:type_range_program,1,2|array|min:1',
            'dates.*'           => 'required',
            'dates.*.date_init' => 'required_if:period_current_program,0|date_format:Y-m-d',
            'dates.*.date_end'  => 'required_if:period_current_program,0|date_format:Y-m-d',
            'dates.*.day_init'  => 'required_if:period_current_program,1|numeric|min:0|max:6',
            'dates.*.day_end'   => 'required_if:period_current_program,1|numeric|min:0|max:6',

            'images'            => 'required|array|min:1',
            'images.*.image'    => 'required|string',
            'images.*.id_asset' => 'nullable|integer|exists:assets,id_asset',
        ];
    }
}
