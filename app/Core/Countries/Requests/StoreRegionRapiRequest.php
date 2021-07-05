<?php

namespace App\Core\Countries\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegionRapiRequest extends FormRequest
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
     *     definition="StoreRegionRapi",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="Region name"
     *     ),
     *     @SWG\Property(
     *       property="countries",
     *       @SWG\Items(
     *          type="object",
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/StoreRegionRapiCountry"),
     *          }
     *        )
     *     ),
     *  ),
     */

    /**
     * @SWG\Definition(
     *     definition="StoreRegionRapiCountry",
     *     @SWG\Property(
     *       property="id_country",
     *       type="integer",
     *       example=1
     *     ),
     *  ),
     */

    public function rules()
    {
        return [
            'name' => 'required|string',

            'countries'                 => 'required|array|min:1',
            'countries.*'               => 'required',
            'countries.*.id_country'    => 'required|integer',
        ];
    }
}
