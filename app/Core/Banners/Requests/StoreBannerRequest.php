<?php

namespace App\Core\Banners\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Swagger\Annotations as SWG;

class StoreBannerRequest extends FormRequest
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
     *     definition="StoreBannerRequest",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="name banner"
     *     ),
     *     @SWG\Property(
     *       property="status",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type_product",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="image",
     *       type="string",
     *       example="https://rapi-reports-stage-public.s3.eu-central-1.amazonaws.com/slides/dashboard1599062424.png"
     *     ),
     *     @SWG\Property(
     *       property="link",
     *       type="string",
     *       example="https://google.com/q?=test-banner-link"
     *     ),
     *
     *     @SWG\Property(
     *       property="regions",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreBannerRegionRapi"),
     *          }
     *        )
     *     ),
     *     @SWG\Property(
     *       property="config",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreBannerConfig"),
     *          }
     *        )
     *     ),
     *
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBannerRegionRapi",
     *     @SWG\Property(
     *       property="id_region",
     *       type="integer",
     *       example=1
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBannerConfig",
     *     @SWG\Property(
     *       property="title",
     *       type="string",
     *       example="title banner"
     *     ),
     *     @SWG\Property(
     *       property="subtitle",
     *       type="string",
     *       example="subtitle banner"
     *     ),
     *     @SWG\Property(
     *       property="id_language",
     *       type="integer",
     *       example=1
     *     ),
     *  ),
     */

    public function rules()
    {
        return [

            'name'         => 'required|string|min:1|max:150',
            'active'       => 'required|numeric|in:0,1',
            'status'       => 'required|numeric|in:0,1',
            'type'         => 'required|numeric|in:0,1',
            'type_product' => 'required|numeric',
            'image'        => 'required_if:type,1|string|max:150',
            'link'         => 'required|string|max:250',

            'regions'               => 'nullable|array|min:1',
            'regions.*'             => 'required',
            'regions.*.id_region'   => 'required|integer|exists:regions,id_region',

            'config'               => 'required|array|min:1',
            'config.*'             => 'required',
            'config.*.title'       => 'required|string|min:1|max:150',
            'config.*.subtitle'    => 'nullable|string|min:0|max:4294967295',
            'config.*.id_language' => 'required|integer|exists:languages,id_language',

        ];
    }
}
