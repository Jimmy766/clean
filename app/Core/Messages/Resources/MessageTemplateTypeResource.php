<?php

namespace App\Core\Messages\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="MessageTemplateType",
 *     required={"identifier","name", "image",*    },
 *  ),
 */
class MessageTemplateTypeResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'type_id'            => $this->type_id,
            'type_name'          => $this->type_name,
        ];
    }

}
