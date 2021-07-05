<?php

namespace App\Core\Base\Services;

use App\Core\Base\Traits\ApiResponser;
use Illuminate\Support\Collection;

class SetTransformerService
{
    use ApiResponser;

    /**
     * @param Collection $collection
     * @param null       $transformerExternal
     * @return array
     */
    public function execute(Collection $collection, $transformerExternal = null): array
    {
        if ($collection->isEmpty()) {
            $collection = ['data'=> []];
            return array_key_exists('data', $collection) === true ? $collection[ 'data'] : [];
        }
        if (is_array($collection->first())) {
            $transformer = null;
        } else {
            $transformer = $collection->first()->transformer;
        }
        if ($transformerExternal !== null) {
            $transformer = $transformerExternal;
        }
        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->transformData($collection, $transformer);
        $data       = array_key_exists('data', $collection) === true ? $collection[ 'data' ] : [];

        if (array_key_exists('pixels', $data)) {
            $pixels = $data[ 'pixels' ];
            unset($data[ 'pixels' ]);
            $newData[ 'data' ]   = $data;
            $newData[ 'pixels' ] = $pixels;
            $data                = $newData;
        }

        return $data;
    }
}
