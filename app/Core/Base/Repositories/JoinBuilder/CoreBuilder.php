<?php

namespace App\Core\Base\Repositories\JoinBuilder;

use App\Core\Base\Repositories\UtilsBuilder\CacheQueryTrait;
use Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CoreBuilder extends Builder
{
    use CacheQueryTrait;

    /**
     * @param int      $perPage
     * @param string[] $columns
     * @param string   $pageName
     * @param null     $page
     * @return LengthAwarePaginator
     */
    public function paginateByRequest(
        $perPage = 15,
        $columns = [ '*' ],
        $pageName = 'page',
        $page = null
    ): LengthAwarePaginator {

        [$perPage, $page] = $this->getParametersPagination($perPage, $page);
        return $this->paginate($perPage, $columns, $pageName, $page);
    }

    public function paginateFromCacheByRequest(
        array $columns = [ '*' ],
        $tag = null,
        int $time = null,
        $perPage = 15,
        $pageName = 'page',
        $page = null
    ): LengthAwarePaginator {
        $query     = $this;
        [$perPage, $page] = $this->getParametersPagination($perPage, $page);
        $extras=$perPage.'-'.$page;
        $nameCache = $this->generateNameCache($query, $columns,$extras);
        if ($time === null) {
            $time = config('constants.cache_5');
        }

        if (request()->force_not_cache != null) {
            return $this->paginateByRequest($perPage, $columns, $pageName, $page);
        }

        return Cache::tags($tag)->remember(
            $nameCache, $time, function () use ($page, $pageName, $columns, $perPage) {
            return $this->paginateByRequest($perPage, $columns, $pageName, $page);
        }
        );
    }

    public function getParametersPagination($perPage, $page)
    {

        $sizeRequest = request()->size_pagination;
        $perPage     = $sizeRequest === null ? $perPage : $sizeRequest;

        $pageRequest = request()->page_pagination;
        $page        = $pageRequest === null ? $page : $pageRequest;

        return [$perPage, $page];
    }

}
