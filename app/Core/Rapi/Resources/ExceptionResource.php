<?php

namespace App\Core\Rapi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Exception",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Exception identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       example="name block"
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
 *       example="value"
 *     ),
 *  ),
 */
class ExceptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id_exception' => $this->id_exception,
            'name'         => $this->name,
            'active'       => $this->active,
            'type'         => $this->type,
            'value'        => $this->value,
        ];
    }
}
