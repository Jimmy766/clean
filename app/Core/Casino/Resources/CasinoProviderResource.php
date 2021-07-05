<?php

namespace App\Core\Casino\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CasinoProviderResource extends JsonResource
{


    /**
     *   @SWG\Definition(
     *     definition="CasinoProvider",
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       format="int32",
     *       description="ID elements identifier",
     *       example="6"
     *     ),
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       description="Provider Name",
     *       example="Multi Slot"
     *     ),
     *   )
     */


    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'identifier' => (integer)$this->id,
            'name' => (string)$this->name
        ];
    }
}
