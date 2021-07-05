<?php

namespace App\Core\Messages\Resources;

use App\Core\Base\Traits\UtilsFormatText;
use Swagger\Annotations as SWG;
use App\Core\Messages\Resources\MessageTemplateResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SWG\Definition(
 *     definition="MessageTemplateLanguage",
 *     required={"identifier","name", "image",*    },
 *  ),
 */
class MessageTemplateLanguageResource extends JsonResource
{

    use UtilsFormatText;

    public function toArray($request)
    {
        return [
            'temp_lang_id'           => $this->temp_lang_id,
            'subject'                => $this->convertTextCharset($this->subject),
            'template_id'            => MessageTemplateResource::collection($this->whenLoaded('messageTemplate')),
            'body'                   => $this->convertTextCharset($this->body),
            'site_id'                => $this->site_id,
            'date_added'             => $this->date_added,
            'language'               => $this->language,
            'from_email_id'          => $this->from_email_id,
        ];
    }

}
