<?php

namespace App\Core\Base\Services;

use Illuminate\Support\Collection;

/**
 * Class SetTransformModelOrCollectionService
 * @package App\Services
 */
class SetTransformToModelOrCollectionService
{

    public static function execute($modelOrCollection)
    {
        if (is_a($modelOrCollection, Collection::class)) {
            return self::collection($modelOrCollection);
        }

        return self::model($modelOrCollection);
    }

    /**
     * @param $model
     * @return null
     */
    private static function model($model)
    {
        return $model ? $model->transformer::transform($model) : null;
    }

    /**
     * @param $collection
     * @return Collection
     */
    private static function collection($collection): Collection
    {
        return $collection->map(
            static function ($item) {
                return $item->transformer ? $item->transformer::transform($item) : $item;
            }
        );
    }

}
