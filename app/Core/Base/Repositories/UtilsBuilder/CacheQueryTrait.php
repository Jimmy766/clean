<?php

namespace App\Core\Base\Repositories\UtilsBuilder;

use App\Core\Base\Repositories\JoinBuilder\CoreBuilder;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Trait CacheQueryTrait
 * @package App\Core\Base\Repositories\UtilsBuilder
 */
trait CacheQueryTrait
{

    /**
     * @param string[]    $columns
     * @param string|null $tag
     * @param int         $time
     * @return Collection
     */
    public function getFromCache(
        array $columns = [ '*' ],
        $tag = null,
        int $time = null
    ): Collection {
        /** @var CoreBuilder $this */
        $query     = $this;
        $tag       = $this->setTag($tag);
        $nameCache = $this->generateNameCache($query, $columns);
        [
            $data,
            $dataIsFromCache,
        ] = $this->getDataFromCacheOrDatabase($query, $columns, $nameCache, $tag);
        $this->setCache($nameCache, $data, $time, $dataIsFromCache, $tag);

        return $data;

    }

    public function setTag($tag = null)
    {
        $tagDefault = config('constants.cache_tag_name');
        if (is_string($tag)) {
            return [ $tagDefault, $tag ];
        }
        if (is_array($tag)) {
            $arrayReturn = [ $tagDefault ];
            return array_merge($arrayReturn, $tag);
        }

        return [ $tagDefault ];
    }

    /**
     * @param string[] $columns
     * @param null     $tag
     * @param null     $time
     * @return Model|null
     */
    public function firstFromCache(
        array $columns = [ '*' ],
        $tag = null,
        int $time = null
    ): ?Model {
        /** @var CoreBuilder $this */
        $query     = $this;
        $tag       = $this->setTag($tag);
        $nameCache = $this->generateNameCache($query, $columns);
        [
            $data,
            $dataIsFromCache,
        ] = $this->firstDataFromCacheOrDatabase($query, $columns, $nameCache, $tag);
        $this->setCache($nameCache, $data, $time, $dataIsFromCache, $tag);

        return $data === '' ? null : $data;
    }

    public function generateNameCache(CoreBuilder $query, $columns, $extras = '')
    {
        $querySql = $query->toSql();
        if (is_array($columns)) {
            $columns = array_values($columns);
            $columns = json_encode($columns);
        }

        $relationShip = array_keys($query->getEagerLoads());
        $queryRelationsShip = GetQueryRelationShip::execute($query, $relationShip);
        $relationShip = json_encode($relationShip);
        $parameters   = json_encode($query->getBindings());
        $nameCache    = $querySql;
        $nameCache    .= $parameters;
        $nameCache    .= $relationShip;
        $nameCache    .= $columns;
        $nameCache    .= $queryRelationsShip;

        $charactersToRemove = [
            ' ',
            '?',
            '`',
            ',',
            '"',
            '*',
            '.',
            '[',
            ']',
            '(',
            ')',
        ];

        $nameCache .= '-' . $extras;
        $nameCache = str_replace($charactersToRemove, '', $nameCache);
        $nameCache = md5($nameCache);
        return $nameCache;

    }

    public function firstDataFromCacheOrDatabase(
        CoreBuilder $query,
        $columns,
        $nameCache,
        array $tag
    ) {
        $dataIsFromCache = true;
        $dataFromCache   = $this->getCache($nameCache, $tag);
        if (request()->force_not_cache != null) {
            $dataFromCache = null;
        }

        if ($dataFromCache !== null) {
            return [ $dataFromCache, $dataIsFromCache ];
        }

        $dataIsFromCache  = false;
        $dataFromDatabase = $query->first($columns);
        $dataFromDatabase = $dataFromDatabase === null ? '' : $dataFromDatabase;
        return [ $dataFromDatabase, $dataIsFromCache ];
    }

    public function getDataFromCacheOrDatabase(
        CoreBuilder $query,
        $columns,
        $nameCache,
        array $tag
    ) {
        $dataIsFromCache = true;
        $dataFromCache   = $this->getCache($nameCache, $tag);
        if (request()->force_not_cache != null) {
            $dataFromCache = null;
        }

        if ($dataFromCache !== null) {
            return [ $dataFromCache, $dataIsFromCache ];
        }

        $dataIsFromCache  = false;
        $dataFromDatabase = $query->get($columns);
        return [ $dataFromDatabase, $dataIsFromCache ];
    }

    public function getCache($nameCache, array $tag)
    {
        return Cache::tags($tag)->get($nameCache);
    }

    public function setCache(
        $nameCache,
        $data,
        $time,
        $dataIsFromCache,
        array $tag
    ): void {
        if ($dataIsFromCache === false) {
            if ($time === null) {
                $time = $this->getModelTimeConstant($this);
            }
            Cache::tags($tag)->put($nameCache, $data, $time);
        }
    }

    /**
     * @param $itemThis
     * @return Repository|Application|mixed
     */
    private function getModelTimeConstant($itemThis)
    {
        $model = $itemThis->getModel();
        $time = config('constants.cache_seconds');

        if (defined($model->getMorphClass()."::TIME_CACHE_MODEL")) {
                $time = $model::TIME_CACHE_MODEL;
        }

        return $time;
    }
}
