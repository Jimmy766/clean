<?php

namespace App\Core\Skins\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkinRequest extends FormRequest
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
     *     definition="StoreSkin",
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="name skin"
     *     ),
     *     @SWG\Property(
     *       property="status",
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
     *              @SWG\Schema(ref="#/definitions/StoreSkinRegionRapi"),
     *          }
     *        )
     *     ),
     *     @SWG\Property(
     *       property="dates",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSkinDates"),
     *          }
     *        )
     *     ),
     *     @SWG\Property(
     *       property="config",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSkinConfig"),
     *          }
     *        )
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSkinRegionRapi",
     *     @SWG\Property(
     *       property="id_region",
     *       type="integer",
     *       example=1
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSkinConfig",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="config skin language english"
     *     ),
     *     @SWG\Property(
     *       property="description",
     *       type="string",
     *       example="description config language english"
     *     ),
     *     @SWG\Property(
     *       property="id_language",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="files",
     *       type="array",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSkinConfigFiles"),
     *          }
     *       )
     *     ),
     *     @SWG\Property(
     *       property="texts",
     *       type="array",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreSkinConfigTexts"),
     *          }
     *       )
     *     ),
     *  ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSkinDates",
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
     *     definition="StoreSkinConfigFiles",
     *     @SWG\Property(
     *       property="tag",
     *       type="string",
     *       example="#image_header"
     *     ),
     *     @SWG\Property(
     *       property="file",
     *       type="string",
     *       example="https://rapi-reports-stage-public.s3.eu-central-1.amazonaws.com/slides/dashboard1599062424.png"
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreSkinConfigTexts",
     *     @SWG\Property(
     *       property="tag",
     *       type="string",
     *       example="#image_header"
     *     ),
     *     @SWG\Property(
     *       property="text",
     *       type="string",
     *       example="https://rapi-reports-stage-public.s3.eu-central-1.amazonaws.com/slides/dashboard1599062424.png"
     *     ),
     *  ),
     */

    public function rules()
    {
        return [

            'name' => 'required|string|max:200',
            'active' => 'required|numeric|min:0|max:1',
            'status' => 'required|numeric|min:0|max:1',

            'regions'             => 'required|array|min:1',
            'regions.*'           => 'required',
            'regions.*.id_region' => 'required|integer|exists:regions,id_region',

            'config'               => 'required|array|min:1',
            'config.*'             => 'required',
            'config.*.name'        => 'required|string|min:1|max:150',
            'config.*.description' => 'nullable|string|min:0|max:4294967295',
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

            'config.*.files'        => 'required|array|min:1',
            'config.*.files.*.tag'  => 'required|string|max:250',
            'config.*.files.*.file' => 'required|string|max:250',

            'config.*.texts'        => 'required|array|min:1',
            'config.*.texts.*.tag'  => 'required|string|max:250',
            'config.*.texts.*.text' => 'required|string|max:4294967295',
        ];
    }
}
