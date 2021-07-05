<?php

namespace App\Core\Messages\Resources;

use App\Core\Base\Traits\UtilsFormatText;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Messages",
 *     required={"message_id","message_body","batch_id",
 *       "message_date_read","message_date_received",
 *       "message_deleted","message_read",
 *       "message_subject","message_type",
 *       "usr_id"
 *      },
 *     @SWG\Property(
 *       property="message_id",
 *       type="integer",
 *       description="message_id of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="message_body",
 *       type="string",
 *       description="message_body of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="batch_id",
 *       type="integer",
 *       description="batch_id of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="message_date_read",
 *       type="string",
 *       description="message_date_read of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="message_date_received",
 *       type="string",
 *       description="message_date_received of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="message_deleted",
 *       type="string",
 *       description="message_deleted of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="message_read",
 *       type="string",
 *       description="message_read of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="message_subject",
 *       type="string",
 *       description="message_subject of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="message_type",
 *       type="integer",
 *       description="message_type of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="usr_id",
 *       type="string",
 *       description="usr_id of message",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="expire",
 *       type="string",
 *       description="expire date of message",
 *       example="2019-05-17"
 *     ),
 *  ),
 */
class MessageResource extends JsonResource
{
    use UtilsFormatText;

    public function toArray($request)
    {
        return [
            'message_id'            => $this->message_id,
            'batch_id'              => $this->batch_id,
            'message_body'          => $this->convertTextCharset($this->message_body),
            'message_date_read'     => $this->message_date_read,
            'message_date_received' => $this->message_date_received,
            'message_deleted'       => $this->message_deleted,
            'message_read'          => $this->message_read,
            'message_subject'       => $this->convertTextCharset($this->message_subject),
            'message_type'          => $this->message_type,
            'usr_id'                => $this->usr_id,
            'expire'                => $this->batch()->firstorFail()->final_date
        ];
    }

}
