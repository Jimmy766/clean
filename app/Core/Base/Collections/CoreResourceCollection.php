<?php

namespace App\Core\Base\Collections;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CoreResourceCollection extends ResourceCollection
{

    public function toArray($request)
    {
        return [
            'resource'   => $this->collection,
            'pagination' => [
                'per_page'       => (int) $this->perPage(),
                'from'           => $this->firstItem(),
                'to'             => $this->lastItem(),
                'total'          => (int) $this->total(),
                'current'        => (int) $this->currentPage(),
                'last_page'      => (int) $this->lastPage(),
                'current_page'   => $this->currentPage(),
                'first_page_url' => $this->url(1),
                'next_page_url'  => $this->nextPageUrl(),
                'prev_page_url'  => $this->previousPageUrl(),
            ],
        ];
    }
}
