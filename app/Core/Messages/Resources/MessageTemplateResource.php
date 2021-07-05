<?php

namespace App\Core\Messages\Resources;

use App\Core\Base\Traits\UtilsFormatText;
use Swagger\Annotations as SWG;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Core\Messages\Resources\MessageTemplateTypeResource;
use App\Core\Messages\Resources\MessageTemplateCategoryResource;
use App\Core\Messages\Resources\MessageTemplateLanguageResource;

/**
 * @SWG\Definition(
 *     definition="MessageTemplate",
 *     @SWG\Property(
 *       property="template_id",
 *       type="integer"
 *     ),
 *     @SWG\Property(
 *       property="template_name",
 *       type="string"
 *     ),
 *     @SWG\Property(
 *       property="template_type",
 *       type="integer",
 *     ),
 *     @SWG\Property(
 *       property="template_category",
 *       type="integer",
 *     ),
 *     @SWG\Property(
 *       property="template_language",
 *       type="integer",
 *     ),
 *     @SWG\Property(
 *       property="template_csv_tags",
 *       type="string",
 *     ),
 *     @SWG\Property(
 *       property="sys_id",
 *       type="integer",
 *     ),
 *     @SWG\Property(
 *       property="template_active",
 *       type="integer",
 *     ),
 *  ),
 */
class MessageTemplateResource extends JsonResource
{

    use UtilsFormatText;
    public function toArray($request)
    {
        return [
            'template_id'           => $this->template_id,
            'template_name'         => $this->convertTextCharset($this->template_name),
            'template_type'         => new MessageTemplateTypeResource($this->whenLoaded('messageTemplateType')),
            'template_category'     => new MessageTemplateCategoryResource($this->whenLoaded('messageTemplateCategory')),
            'template_language'     => \App\Core\Messages\Resources\MessageTemplateLanguageResource::collection($this->whenLoaded('messageTemplateLanguage')),
            'template_csv_tags'     => $this->template_csv_tags,
            'sys_id'                => $this->sys_id,
            'template_active'       => $this->template_active,
        ];
    }

}
