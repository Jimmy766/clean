<?php

namespace App\Core\Base\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheRedisTraits
{
    /**
     * @param $key
     */
    protected function forgetCacheByKey($key): void
    {
        Cache::forget($key);
    }

    /**
     * @param $tag
     */
    protected function forgetCacheByTag($tag): void
    {
        Cache::tags($tag)->flush();
    }
}
