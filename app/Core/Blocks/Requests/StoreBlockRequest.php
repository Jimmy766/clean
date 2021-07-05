<?php

namespace App\Core\Blocks\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Swagger\Annotations as SWG;

class StoreBlockRequest extends FormRequest
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
     *     definition="StoreBlockRequest",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="example name block"
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=3
     *     ),
     *     @SWG\Property(
     *       property="id_entityable",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type_entity",
     *       type="integer",
     *       example=0
     *     ),
     *     @SWG\Property(
     *       property="value",
     *       type="string",
     *       example="example value"
     *     ),
     *     @SWG\Property(
     *       property="id_blockable",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type_block",
     *       type="integer",
     *       example=0
     *     ),
     *  ),
     */

    public function rules()
    {
        return [

            'name'            => 'required|string|min:1|max:150',
            'active'          => 'required|numeric|in:0,1',
            'type'            => 'required|numeric',
            'value'           => 'nullable|string|max:250',
            'id_entityable'   => 'nullable|numeric',
            'type_entity'     => 'required_with:id_entityable|numeric',
            'id_blockable'    => 'nullable|numeric',
            'type_block'      => 'required_with:id_blockable|numeric',

        ];
    }

//    EXAMPLE OTHER REQUESTS

    /**
     * @SWG\Definition(
     *     definition="StoreBlockExamplesRequest",
     *     @SWG\Property(
     *       property="example store block by IP",
     *       description="example store block by IP",
     *       type="object",
     *       allOf={
     *         @SWG\Schema(ref="#/definitions/StoreBlockRequestByIp"),
     *       }
     *     ),
     *     @SWG\Property(
     *       property="example store block by Region",
     *       description="example store block by Region",
     *       type="object",
     *       allOf={
     *         @SWG\Schema(ref="#/definitions/StoreBlockRequestByRegion"),
     *       }
     *     ),
     *     @SWG\Property(
     *       property="example store block by Language",
     *       description="example store block by Language",
     *       type="object",
     *       allOf={
     *         @SWG\Schema(ref="#/definitions/StoreBlockRequestByLanguage"),
     *       }
     *     ),
     *     @SWG\Property(
     *       property="example store block by Affiliate",
     *       description="example store block by Affiliate",
     *       type="object",
     *       allOf={
     *         @SWG\Schema(ref="#/definitions/StoreBlockRequestByAffiliate"),
     *       }
     *     ),
     *     @SWG\Property(
     *       property="example store block by One Product",
     *       description="example store block by One Product",
     *       type="object",
     *       allOf={
     *         @SWG\Schema(ref="#/definitions/StoreBlockRequestByOneProduct"),
     *       }
     *     ),
     *     @SWG\Property(
     *       property="example store block by List Product",
     *       description="example store block by List Product",
     *       type="object",
     *       allOf={
     *         @SWG\Schema(ref="#/definitions/StoreBlockRequestByListProduct"),
     *       }
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBlockRequestByIp",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="block by IP",
     *       description="name of block"
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=0
     *     ),
     *     @SWG\Property(
     *       property="value",
     *       type="string",
     *       example="120.120.120.11"
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBlockRequestByRegion",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="block by Region"
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="id_blockable",
     *       type="integer",
     *       example=4
     *     ),
     *     @SWG\Property(
     *       property="type_block",
     *       type="integer",
     *       example=0
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBlockRequestByLanguage",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="block by Language"
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=2
     *     ),
     *     @SWG\Property(
     *       property="id_blockable",
     *       type="integer",
     *       example=4
     *     ),
     *     @SWG\Property(
     *       property="type_block",
     *       type="integer",
     *       example=0
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBlockRequestByAffiliate",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="block by Affiliate"
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=3
     *     ),
     *     @SWG\Property(
     *       property="value",
     *       type="string",
     *       example="codeAffiliate"
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBlockRequestByOneProduct",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="block by one product"
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=4
     *     ),
     *     @SWG\Property(
     *       property="id_entityable",
     *       type="integer",
     *       example=2
     *     ),
     *     @SWG\Property(
     *       property="type_entity",
     *       type="integer",
     *       example=0
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreBlockRequestByListProduct",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="block by list product"
     *     ),
     *     @SWG\Property(
     *       property="active",
     *       type="integer",
     *       example=1
     *     ),
     *     @SWG\Property(
     *       property="type",
     *       type="integer",
     *       example=5
     *     ),
     *     @SWG\Property(
     *       property="value",
     *       type="string",
     *       example="/lotteries"
     *     ),
     *  ),
     */
}
