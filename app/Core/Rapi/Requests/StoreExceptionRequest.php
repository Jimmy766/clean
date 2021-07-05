<?php

namespace App\Core\Rapi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Swagger\Annotations as SWG;

class StoreExceptionRequest extends FormRequest
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
     *     definition="StoreExceptionRequest",
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="name exception"
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
     *       example="120.120.120.12"
     *     ),
     *  ),
     */

    public function rules()
    {
        return [
            'name'            => 'required|string|min:1|max:150',
            'active'          => 'required|numeric|in:0,1',
            'type'            => 'required|numeric',
            'value'           => 'required|string|max:250',
        ];
    }
}
