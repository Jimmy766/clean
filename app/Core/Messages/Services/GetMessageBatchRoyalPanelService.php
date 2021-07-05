<?php

namespace App\Core\Messages\Services;

use App\Core\Messages\Models\MessageBatch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetMessageBatchRoyalPanelService
{

	public function execute(Request $request)
	{
		$system = $request->system;
		$date_init  = $request->date_init;
		$date_end  = $request->date_end;
		$status  = $request->status;
		$category  = $request->category;

        $columns = [
            'messages_batch.batch_id',
            'messages_batch.batch_recipients',
            'messages_batch.template_id',
            'messages_batch.batch_date_created',
            'messages_batch.batch_date_scheduled',
            'messages_batch.batch_date_sent',
            'messages_batch.batch_status',
            'messages_batch.final_date'
        ];

		$relations = ['messageTemplate', 'messageTemplate.messageTemplateCategory'];

		$messageBatchQuery     = MessageBatch::with($relations);
        $messageBatchQuery=$this->querySystem($messageBatchQuery,$system);
        $messageBatchQuery=$this->queryCategories($messageBatchQuery,$category);
        $messageBatchQuery=$this->queryDateRange($messageBatchQuery,$date_init,$date_end);
        $messageBatchQuery=$this->queryStatus($messageBatchQuery,$status);

		return $messageBatchQuery->paginateFromCacheByRequest($columns,MessageBatch::TAG_CACHE_MODEL);
	}

	private function queryCategories($messageBatchQuery, $category)
	{
		if($category!==null){
			$messageBatchQuery->whereHas('messageTemplate.messageTemplateCategory', function ($query) use ($category){
				$query->where('messages_templates_categories.category_id',$category);
			});
		}
		return $messageBatchQuery;
	}
	private function querySystem($messageBatchQuery, $system)
	{
		if($system!==null){
            $messageBatchQuery=$messageBatchQuery->whereHas('messageTemplate', function ($query) use ($system){
				$query->where('messages_templates.sys_id',$system);
			});
		}
		return $messageBatchQuery;
	}
	private function queryStatus($messageBatchQuery, $status)
	{
		if($status!==null){
            $messageBatchQuery=$messageBatchQuery->where('messages_batch.batch_status', $status);
		}
		return $messageBatchQuery;
	}
	private function queryDateRange($messageBatchQuery,$date_init,$date_end)
	{
	    if(isset($date_init,$date_end)){
	        $date_init=new Carbon($date_init);
	        $date_end=new Carbon($date_end);
	        $date_end->addDay(1);
            $messageBatchQuery=$messageBatchQuery->whereHas('messageTemplate.messageTemplateCategory', function ($query) use ($date_init,$date_end) {
                $query->whereBetween('messages_batch.batch_date_created', [$date_init->toDateString().' 00:00:00',$date_end->toDateString()]);
            });
        }
        return $messageBatchQuery;
	}


}
