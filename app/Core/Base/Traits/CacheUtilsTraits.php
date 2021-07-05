<?php

namespace App\Core\Base\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheUtilsTraits
{
    protected function forgetCacheByKey($keyCustoms){
        Cache::forget($keyCustoms);
    }

    /**
     * @param $tag
     */
    protected function forgetCacheByTag($tag): void
    {
        Cache::tags($tag)->flush();
    }
}
