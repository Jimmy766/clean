<?php

namespace App\Core\Blocks\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Block",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Block identifier",
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
 *       example=3
 *     ),
 *     @SWG\Property(
 *       property="value",
 *       type="string",
 *       example="value"
 *     ),
 *     @SWG\Property(
 *       property="id_entityable",
 *       type="integer",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="entityable",
 *       type="string",
 *       example=""
 *     ),
 *     @SWG\Property(
 *       property="id_blockable",
 *       type="integer",
 *       example=1
 *     ),
 *     @SWG\Property(
 *       property="blockable",
 *       type="string",
 *       example=""
 *     ),
 *  ),
 */
class BlockResource extends JsonResource
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
            'id_block'      => $this->id_block,
            'name'          => $this->name,
            'active'        => $this->active,
            'type'          => $this->type,
            'value'         => $this->value,
            'id_entityable' => $this->id_entityable,
            'entityable'    => $this->validateEntityableExist(),
            'id_blockable' => $this->id_blockable,
            'blockable'     => $this->validateBlockableExist(),
        ];
    }

    private function validateBlockableExist()
    {
        if (property_exists($this->resource, 'blockable')) {
            return $this->blockable;
        }

        return $this->whenLoaded('blockable');
    }

    private function validateEntityableExist()
    {
        if (property_exists($this->resource, 'entityable')) {
            return $this->entityable;
        }

        return $this->whenLoaded('entityable');
    }
}
