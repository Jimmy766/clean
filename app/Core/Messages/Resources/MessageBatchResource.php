<?php

    namespace App\Core\Messages\Resources;

    use App\Core\Messages\Resources\MessageTemplateResource;
    use Swagger\Annotations as SWG;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @SWG\Definition(
     *     definition="MessageBatch",
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       description="Batch identifier",
     *       example="3"
     *     ),
     *     @SWG\Property(
     *       property="template_id",
     *       type="integer",
     *       description="Template ID",
     *     ),
     *     @SWG\Property(
     *       property="sus_id",
     *       type="integer",
     *       description="Sender ID",
     *     ),
     *     @SWG\Property(
     *       property="batch_date_created",
     *       type="string",
     *       description="Date Created",
     *     ),
     *     @SWG\Property(
     *       property="batch_date_scheduled",
     *       type="string",
     *       description="Date Scheduled",
     *     ),
     *     @SWG\Property(
     *       property="batch_date_sent",
     *       type="string",
     *       description="Date Sent",
     *     ),
     *     @SWG\Property(
     *       property="final_date",
     *       type="string",
     *       description="Date Final",
     *     ),
     *     @SWG\Property(
     *       property="batch_status",
     *       type="integer",
     *       description="Status",
     *     ),
     *  ),
     */
    class MessageBatchResource extends JsonResource
    {


        public function toArray($request)
        {
            return [
                'batch_id'              => $this->batch_id,
                'batch_recipients'      => $this->batch_recipients,
                'template'              => new MessageTemplateResource($this->whenLoaded('messageTemplate')),
                'batch_date_created'    => $this->batch_date_created,
                'batch_date_scheduled'  => $this->batch_date_scheduled,
                'batch_date_sent'       => $this->batch_date_sent,
                'final_date'            => $this->final_date,
                'batch_status'          => $this->batch_status
            ];
        }

    }
