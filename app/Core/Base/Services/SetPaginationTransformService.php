<?php

namespace App\Core\Base\Services;

use App\Core\Base\Traits\ApiResponser;
use Illuminate\Pagination\LengthAwarePaginator;

class SetPaginationTransformService
{
    use ApiResponser;

    /**
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    public function execute(LengthAwarePaginator $paginator): array
    {
        $transformer = null;
        SendLogUserRequestResponseService::execute($paginator);
        $collection = collect($paginator->items());
        if(!is_array($collection->first()) && !$collection->isEmpty()){
            $transformer = $collection->first()->transformer;
        }
        $collection    = $this->transformData($collection, $transformer);
        if(count($collection) > 0){
            $collection = array_key_exists('data', $collection) ? $collection['data'] : $collection;
        }
        return [
            'resource'   => $collection,
            'pagination' => [
                'per_page'       => (int) $paginator->perPage(),
                'from'           => $paginator->firstItem(),
                'to'             => $paginator->lastItem(),
                'total'          => (int) $paginator->total(),
                'current'        => (int) $paginator->currentPage(),
                'last_page'      => (int) $paginator->lastPage(),
                'current_page'   => $paginator->currentPage(),
                'first_page_url' => $paginator->url(1),
                'next_page_url'  => $paginator->nextPageUrl(),
                'prev_page_url'  => $paginator->previousPageUrl(),
            ],
        ];
    }

}
