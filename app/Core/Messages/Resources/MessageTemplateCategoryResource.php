<?php

namespace App\Core\Messages\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="MessageTemplateCategory",
 *     required={"identifier","name", "image",*    },
 *  ),
 */
class MessageTemplateCategoryResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'category_id'            => $this->category_id,
            'category_name'          => $this->category_name,
        ];
    }

}
